<?php

require_once 'scheduledccrecipients.civix.php';
use CRM_Scheduledccrecipients_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function scheduledccrecipients_civicrm_config(&$config) {
  _scheduledccrecipients_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function scheduledccrecipients_civicrm_install() {
  _scheduledccrecipients_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function scheduledccrecipients_civicrm_enable() {
  _scheduledccrecipients_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_buildForm
 */
function scheduledccrecipients_civicrm_buildForm($formName, &$form) {
  if ($formName == "CRM_Admin_Form_ScheduleReminders" && ($form->getAction() & CRM_Core_Action::ADD || $form->getAction() & CRM_Core_Action::UPDATE)) {
    $fillReminderCustomFields = FALSE;
    $locationTypeId = '';

    if ($form->getVar('_id')) {
      $values = $form->getVar('_values');

      $data = civicrm_api3("ScheduledReminderData", "get", [
        "reminder_id" => $values["id"],
      ]);

      $ccValues = [];
      $bccValues = [];

      if ($data["count"]) {
        $fillReminderCustomFields = TRUE;
        $ccValues = array_column($data["values"], "email_cc");
        $bccValues = array_column($data["values"], "email_bcc");
        $locationTypeId = (string) (array_values($data["values"])[0]["location_type_id"] ?? '');
      }

      $form->assign('cc_values', $ccValues);
      $form->assign('bcc_values', $bccValues);
    }

    $form->assign('location_type_value', $locationTypeId ?? '');
    $form->assign('fill_reminder_custom_fields', $fillReminderCustomFields);

    $form->addEntityRef('email_cc', ts('CC'), [
        'entity' => 'Contact',
        'placeholder' => ts('- Select Contacts -'),
        'select' => ['minimumInputLength' => 0, 'multiple' => TRUE],
    ]);

    $form->addEntityRef('email_bcc', ts('BCC'), [
        'entity' => 'Contact',
        'placeholder' => ts('- Select Contacts -'),
        'select' => ['minimumInputLength' => 0, 'multiple' => TRUE],
    ]);

    $form->add('select', 'location_type_id', ts('Email Location Type'),
      CRM_Core_PseudoConstant::get('CRM_Core_DAO_Address', 'location_type_id'),
      FALSE,
      ['class' => 'crm-select2', 'placeholder' => ts('- Primary email -'), 'allowClear' => TRUE]
    );

    CRM_Core_Region::instance('page-body')->add([
        'template' => 'CRM/Scheduledccrecipients/Form/Fields.tpl',
    ]);
  }
}

/**
 * Implements hook_civicrm_postProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postProcess
 *
 * @param $formName string
 * @param $form \CRM_Core_Form
 *
 * @throws \CRM_Core_Exception
 */
function scheduledccrecipients_civicrm_postProcess($formName, &$form) {
  if ($formName == "CRM_Admin_Form_ScheduleReminders") {
    $formid = $form->get('id');
    $emailCCValues = $form->_submitValues['email_cc'] ?? NULL;
    $emailBCCValues = $form->_submitValues['email_bcc'] ?? NULL;

    $params = [
      "reminder_id" => $formid,
      "sequential"  => TRUE,
    ];

    $data = civicrm_api3("ScheduledReminderData", "get", $params);

    if (count($data["values"])) {
      $params["id"] = $data["values"][0]["id"];
    }

    $params["email_cc"] = $emailCCValues;
    $params["email_bcc"] = $emailBCCValues;
    $params["location_type_id"] = ($form->_submitValues['location_type_id'] ?? '') ?: 'null';

    $actionScheduleCount = civicrm_api3('ActionSchedule', 'getcount', [
      'id' => $formid,
    ]);

    if ($actionScheduleCount) {
      civicrm_api3("ScheduledReminderData", "create", $params);
    }
  }
}

/**
 * Implements hook_civicrm_alterMailParams().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterMailParams
 */
function scheduledccrecipients_civicrm_alterMailParams(&$params, $context) {
  // This hook runs for every outgoing mail. Many of them (for example receipts
  // built from message template workflows) carry no groupName, entity or
  // entity_id, so read them defensively. Anything that is not a scheduled
  // reminder is left alone. Core passes entity and entity_id for scheduled
  // reminders itself, so no core patch is needed to recognise them.
  $groupName = $params['groupName'] ?? NULL;
  $entity = $params['entity'] ?? NULL;
  $reminder_id = $params['entity_id'] ?? NULL;

  if ($groupName === "Scheduled Reminder Sender" && $entity === "action_schedule" && $reminder_id) {
    $data = civicrm_api3("ScheduledReminderData", "get", [
      "reminder_id"  => $reminder_id,
      "sequential"   => TRUE,
    ]);

    if ($data["count"] > 0) {
      $data = $data["values"][0];

      $emailCCContacts = $data["email_cc"] ?? "";
      $emailBCCContacts = $data["email_bcc"] ?? "";

      $ccEmails = getEmailsByContacts($emailCCContacts);
      $bccEmails = getEmailsByContacts($emailBCCContacts);

      $params["cc"] = implode(",", $ccEmails);
      $params["bcc"] = implode(",", $bccEmails);

      if (!empty($data["location_type_id"]) && !empty($params["contactId"])) {
        scheduledccrecipients_apply_location_type($params, (int) $params["contactId"], (int) $data["location_type_id"]);
      }
    }
  }
}

/**
 * Send the reminder to the recipient's email of the chosen location type.
 *
 * The primary email is kept when the contact has no usable email of that type
 * (none, or on hold), and when the mail does not go to the contact's primary
 * email (for example when the reminder uses alternate recipients).
 *
 * @param array $params
 *   The mail params, changed in place.
 * @param int $contactId
 * @param int $locationTypeId
 */
function scheduledccrecipients_apply_location_type(array &$params, int $contactId, int $locationTypeId): void {
  $primary = CRM_Contact_BAO_Contact::getPrimaryEmail($contactId, TRUE);
  if (!$primary || ($params['toEmail'] ?? NULL) !== $primary) {
    return;
  }
  $email = \Civi\Api4\Email::get(FALSE)
    ->addSelect('email')
    ->addWhere('contact_id', '=', $contactId)
    ->addWhere('location_type_id', '=', $locationTypeId)
    ->addWhere('on_hold', '=', 0)
    ->addOrderBy('is_primary', 'DESC')
    ->addOrderBy('id', 'ASC')
    ->execute()->first();
  if (!empty($email['email'])) {
    $params['toEmail'] = $email['email'];
  }
}

/**
 * Function to return emails of given contacts
 *
 * @param $emailContacts
 * @return array
 * @throws CiviCRM_API3_Exception
 */
function getEmailsByContacts($emailContacts) {
  $emails = [];

  if (!empty($emailContacts)) {
    $emails = civicrm_api3('Contact', 'get', [
        'sequential' => 1,
        'return' => "email",
        'contact_id' => ['IN' => explode(",", $emailContacts)],
    ]);

    $emails = array_filter(array_column($emails["values"], "email"));
  }
  return $emails;
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 */

// function scheduledccrecipients_civicrm_entityTypes(&$entityTypes) {
//   if (!isset($entityTypes['CRM_Scheduledccrecipients_DAO_ScheduledReminderData'])) {
//     $entityTypes['CRM_Scheduledccrecipients_DAO_ScheduledReminderData'] = [
//       'name' => 'ScheduledReminderData',
//       'class' => 'CRM_Scheduledccrecipients_DAO_ScheduledReminderData',
//       'table' => 'civicrm_scheduledreminderdata',
//     ];
//   }
// }
