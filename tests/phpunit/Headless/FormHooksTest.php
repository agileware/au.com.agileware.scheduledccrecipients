<?php

namespace Civi\Scheduledccrecipients\Tests\Headless;

/**
 * The postProcess hook stores the CC and BCC contacts chosen on the Schedule
 * Reminders form, and the ScheduledReminderData API reads them back.
 *
 * @group headless
 */
class FormHooksTest extends ScheduledccrecipientsTestCase {

  /**
   * A stand-in for CRM_Admin_Form_ScheduleReminders after it was submitted.
   */
  private function submittedForm(int $reminderId, array $submitValues): \CRM_Admin_Form_ScheduleReminders {
    // The form has no controller here, so serve the stored id directly.
    $form = new class($reminderId) extends \CRM_Admin_Form_ScheduleReminders {

      private $reminderId;

      public function __construct($reminderId) {
        $this->reminderId = $reminderId;
      }

      public function get($name) {
        return $name === 'id' ? $this->reminderId : NULL;
      }

    };
    $form->_submitValues = $submitValues;
    return $form;
  }

  public function testPostProcessStoresCcAndBcc(): void {
    $reminderId = $this->createReminder();
    $cc = $this->createContact('cc@example.test');
    $bcc = $this->createContact('bcc@example.test');

    $form = $this->submittedForm($reminderId, ['email_cc' => (string) $cc, 'email_bcc' => (string) $bcc]);
    scheduledccrecipients_civicrm_postProcess('CRM_Admin_Form_ScheduleReminders', $form);

    $data = civicrm_api3('ScheduledReminderData', 'getsingle', ['reminder_id' => $reminderId]);
    $this->assertSame((string) $cc, $data['email_cc']);
    $this->assertSame((string) $bcc, $data['email_bcc']);
  }

  public function testPostProcessUpdatesTheExistingRowInsteadOfAddingOne(): void {
    $reminderId = $this->createReminder();
    $first = $this->createContact('first@example.test');
    $second = $this->createContact('second@example.test');

    foreach ([$first, $second] as $contactId) {
      $form = $this->submittedForm($reminderId, ['email_cc' => (string) $contactId, 'email_bcc' => '']);
      scheduledccrecipients_civicrm_postProcess('CRM_Admin_Form_ScheduleReminders', $form);
    }

    $data = civicrm_api3('ScheduledReminderData', 'get', ['reminder_id' => $reminderId]);
    $this->assertSame(1, $data['count']);
    $this->assertSame((string) $second, array_values($data['values'])[0]['email_cc']);
  }

  public function testPostProcessStoresAndClearsTheLocationType(): void {
    $reminderId = $this->createReminder();
    $workTypeId = (int) civicrm_api3('LocationType', 'getvalue', ['name' => 'Work', 'return' => 'id']);

    $form = $this->submittedForm($reminderId, ['email_cc' => '', 'email_bcc' => '', 'location_type_id' => (string) $workTypeId]);
    scheduledccrecipients_civicrm_postProcess('CRM_Admin_Form_ScheduleReminders', $form);
    $data = civicrm_api3('ScheduledReminderData', 'getsingle', ['reminder_id' => $reminderId]);
    $this->assertSame((string) $workTypeId, (string) $data['location_type_id']);

    $form = $this->submittedForm($reminderId, ['email_cc' => '', 'email_bcc' => '', 'location_type_id' => '']);
    scheduledccrecipients_civicrm_postProcess('CRM_Admin_Form_ScheduleReminders', $form);
    $data = civicrm_api3('ScheduledReminderData', 'getsingle', ['reminder_id' => $reminderId]);
    $this->assertEmpty($data['location_type_id'] ?? NULL);
  }

  public function testPostProcessIgnoresOtherForms(): void {
    $reminderId = $this->createReminder();
    $form = $this->submittedForm($reminderId, ['email_cc' => '1', 'email_bcc' => '1']);
    scheduledccrecipients_civicrm_postProcess('CRM_Admin_Form_Something', $form);
    $this->assertSame(0, civicrm_api3('ScheduledReminderData', 'getcount', ['reminder_id' => $reminderId]));
  }

  public function testPostProcessStoresNothingForAMissingReminder(): void {
    $form = $this->submittedForm(999999, ['email_cc' => '1', 'email_bcc' => '']);
    scheduledccrecipients_civicrm_postProcess('CRM_Admin_Form_ScheduleReminders', $form);
    $this->assertSame(0, civicrm_api3('ScheduledReminderData', 'getcount', ['reminder_id' => 999999]));
  }

}
