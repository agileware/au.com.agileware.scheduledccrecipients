<?php

namespace Civi\Scheduledccrecipients\Tests\Headless;

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;

/**
 * Base class for tests against a headless CiviCRM database.
 *
 * Every test runs inside a transaction that is rolled back.
 *
 * @group headless
 */
abstract class ScheduledccrecipientsTestCase extends TestCase implements HeadlessInterface, TransactionalInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()->installMe(__DIR__)->apply();
  }

  /**
   * Create a contact, optionally with a primary email, and return its id.
   */
  protected function createContact(?string $email = NULL): int {
    $params = ['contact_type' => 'Individual', 'first_name' => 'Cc', 'last_name' => 'Test' . uniqid()];
    if ($email) {
      $params['email'] = $email;
    }
    return (int) civicrm_api3('Contact', 'create', $params)['id'];
  }

  /**
   * Create a scheduled reminder (ActionSchedule) and return its id.
   */
  protected function createReminder(): int {
    $name = 'cc_test_' . uniqid();
    \CRM_Core_DAO::executeQuery(
      'INSERT INTO civicrm_action_schedule (name, title, recipient, start_action_offset, start_action_unit, start_action_condition, start_action_date, is_repeat, is_active, mapping_id, entity_value, body_text, subject)
       VALUES (%1, %1, 1, 1, "day", "after", "activity_date_time", 0, 1, 1, "1", "body", "subject")',
      [1 => [$name, 'String']]
    );
    return (int) \CRM_Core_DAO::singleValueQuery('SELECT id FROM civicrm_action_schedule WHERE name = %1', [1 => [$name, 'String']]);
  }

  /**
   * The parameters core passes to hook_civicrm_alterMailParams for a
   * scheduled reminder, as modified by civcirm-core-reminder-tokens.patch.
   */
  protected function reminderMailParams(int $reminderId): array {
    return [
      'groupName' => 'Scheduled Reminder Sender',
      'from' => 'sender@example.test',
      'toName' => 'To Person',
      'toEmail' => 'to@example.test',
      'subject' => 'Reminder',
      'entity' => 'action_schedule',
      'entity_id' => $reminderId,
      'token_params' => ['id' => 1],
    ];
  }

}
