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
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function scheduledccrecipients_civicrm_xmlMenu(&$files) {
  _scheduledccrecipients_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function scheduledccrecipients_civicrm_postInstall() {
  _scheduledccrecipients_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function scheduledccrecipients_civicrm_uninstall() {
  _scheduledccrecipients_civix_civicrm_uninstall();
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
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function scheduledccrecipients_civicrm_disable() {
  _scheduledccrecipients_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function scheduledccrecipients_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _scheduledccrecipients_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function scheduledccrecipients_civicrm_managed(&$entities) {
  _scheduledccrecipients_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function scheduledccrecipients_civicrm_caseTypes(&$caseTypes) {
  _scheduledccrecipients_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function scheduledccrecipients_civicrm_angularModules(&$angularModules) {
  _scheduledccrecipients_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function scheduledccrecipients_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _scheduledccrecipients_civix_civicrm_alterSettingsFolders($metaDataFolders);
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

      $data = civicrm_api3("ScheduledReminderData", "get", array(
        "reminder_id" => $values["id"],
      ));

      $ccValues = array();
      $bccValues = array();

      if ($data["count"]) {
        $fillReminderCustomFields = TRUE;
        $ccValues = array_column($data["values"], "email_cc");
        $bccValues = array_column($data["values"], "email_bcc");
      }

      $form->assign('cc_values', $ccValues);
      $form->assign('bcc_values', $bccValues);
    }

    $form->assign('fill_reminder_custom_fields', $fillReminderCustomFields);

    $form->addEntityRef('email_cc', ts('CC'), array(
        'entity' => 'Contact',
        'placeholder' => ts('- Select Contacts -'),
        'select' => array('minimumInputLength' => 0, 'multiple' => TRUE),
    ));

    $form->addEntityRef('email_bcc', ts('BCC'), array(
        'entity' => 'Contact',
        'placeholder' => ts('- Select Contacts -'),
        'select' => array('minimumInputLength' => 0, 'multiple' => TRUE),
    ));

    CRM_Core_Region::instance('page-body')->add(array(
        'template' => 'CRM/Scheduledccrecipients/Form/Fields.tpl',
    ));
  }
}

/**
 * Implements hook_civicrm_postProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postProcess
 */
function scheduledccrecipients_civicrm_postProcess($formName, &$form) {
  if ($formName == "CRM_Admin_Form_ScheduleReminders") {
    $formid = $form->get('id');
    $emailCCValues = CRM_Utils_Array::value('email_cc', $form->_submitValues);
    $emailBCCValues = CRM_Utils_Array::value('email_bcc', $form->_submitValues);

    $params = array(
      "reminder_id" => $formid,
      "sequential"  => TRUE,
    );

    $data = civicrm_api3("ScheduledReminderData", "get", $params);

    if (count($data["values"])) {
      $params["id"] = $data["values"][0]["id"];
    }

    $params["email_cc"] = $emailCCValues;
    $params["email_bcc"] = $emailBCCValues;

    civicrm_api3("ScheduledReminderData", "create", $params);
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
    $data = civicrm_api3("ScheduledReminderData", "get", array(
      "reminder_id"  => $reminder_id,
      "sequential"   => TRUE,
    ));

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
  if ($emailContacts != "") {
    $emails = civicrm_api3('Contact', 'get', array(
        'sequential' => 1,
        'return' => "email",
        'contact_id' => array('IN' => explode(",", $emailContacts)),
    ));

    $emails = array_filter(array_column($emails["values"], "email"));
    return $emails;
  }
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 */
function accountsync_civicrm_entityTypes(&$entityTypes) {
  if (!isset($entityTypes['CRM_Scheduledccrecipients_DAO_ScheduledReminderData'])) {
    $entityTypes['CRM_Scheduledccrecipients_DAO_ScheduledReminderData'] = array(
      'name' => 'ScheduledReminderData',
      'class' => 'CRM_Scheduledccrecipients_DAO_ScheduledReminderData',
      'table' => 'civicrm_scheduledreminderdata',
    );
  }
}
