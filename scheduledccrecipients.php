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
      }

      $form->assign('cc_values', $ccValues);
      $form->assign('bcc_values', $bccValues);
    }

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
  if ($params['groupName'] == "Scheduled Reminder Sender" && $params['entity'] == "action_schedule" && isset($params["token_params"])) {
    $reminder_id = $params['entity_id'];
    $data = civicrm_api3("ScheduledReminderData", "get", [
      "reminder_id"  => $reminder_id,
      "sequential"   => TRUE,
    ]);

    if ($data["count"] > 0) {
      $data = $data["values"][0];

      $emailCCContacts = $data["email_cc"];
      $emailBCCContacts = $data["email_bcc"];

      $ccEmails = getEmailsByContacts($emailCCContacts);
      $bccEmails = getEmailsByContacts($emailBCCContacts);

      $params["cc"] = implode(",", $ccEmails);
      $params["bcc"] = implode(",", $bccEmails);
    }
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

  if ($emailContacts != "") {
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
