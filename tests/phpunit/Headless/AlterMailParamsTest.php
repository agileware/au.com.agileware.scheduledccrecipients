<?php

namespace Civi\Scheduledccrecipients\Tests\Headless;

/**
 * hook_civicrm_alterMailParams: adds CC and BCC addresses to scheduled
 * reminders and leaves every other mail alone.
 *
 * PHPUnit is configured to turn warnings and notices into exceptions, so an
 * "Undefined array key" in the hook fails these tests.
 *
 * @group headless
 */
class AlterMailParamsTest extends ScheduledccrecipientsTestCase {

  /**
   * Regression test: receipts built from message template workflows carry no
   * groupName, entity or entity_id. The hook used to warn "Undefined array key
   * groupName" for each of them.
   */
  public function testMailWithoutGroupNameIsLeftAlone(): void {
    $params = ['from' => 'x', 'toEmail' => 'y'];
    $before = $params;
    scheduledccrecipients_civicrm_alterMailParams($params, 'messageTemplate');
    $this->assertSame($before, $params);
  }

  public function testEmptyParamsAreLeftAlone(): void {
    $params = [];
    scheduledccrecipients_civicrm_alterMailParams($params, 'messageTemplate');
    $this->assertSame([], $params);
  }

  public function testGroupNameWithoutEntityIsLeftAlone(): void {
    $params = ['groupName' => 'Scheduled Reminder Sender', 'toEmail' => 'y'];
    $before = $params;
    scheduledccrecipients_civicrm_alterMailParams($params, 'messageTemplate');
    $this->assertSame($before, $params);
  }

  public function testMailFromOtherGroupIsLeftAlone(): void {
    $params = ['groupName' => 'msg_tpl_workflow_contribution', 'entity' => 'action_schedule', 'entity_id' => 1, 'token_params' => []];
    $before = $params;
    scheduledccrecipients_civicrm_alterMailParams($params, 'messageTemplate');
    $this->assertSame($before, $params);
  }

  public function testReminderWithoutTokenParamsStillGetsRecipients(): void {
    $reminderId = $this->createReminder();
    $this->saveData($reminderId, [$this->createContact('cc@example.test')], []);
    $params = $this->reminderMailParams($reminderId);
    unset($params['token_params']);
    scheduledccrecipients_civicrm_alterMailParams($params, 'messageTemplate');
    $this->assertSame('cc@example.test', $params['cc']);
  }

  public function testManyContactsFitInTheColumns(): void {
    $reminderId = $this->createReminder();
    $ids = [];
    for ($i = 0; $i < 8; $i++) {
      $ids[] = $this->createContact("many$i@example.test");
    }
    $this->saveData($reminderId, $ids, $ids);
    $params = $this->reminderMailParams($reminderId);
    scheduledccrecipients_civicrm_alterMailParams($params, 'messageTemplate');
    $this->assertCount(8, explode(',', $params['cc']));
    $this->assertCount(8, explode(',', $params['bcc']));
  }

  public function testReminderWithoutEntityIdIsLeftAlone(): void {
    $params = $this->reminderMailParams(0);
    unset($params['entity_id']);
    $before = $params;
    scheduledccrecipients_civicrm_alterMailParams($params, 'messageTemplate');
    $this->assertSame($before, $params);
  }

  public function testReminderWithNoStoredDataIsLeftAlone(): void {
    $params = $this->reminderMailParams($this->createReminder());
    $before = $params;
    scheduledccrecipients_civicrm_alterMailParams($params, 'messageTemplate');
    $this->assertSame($before, $params);
  }

  public function testReminderGetsCcAndBccAddresses(): void {
    $reminderId = $this->createReminder();
    $cc1 = $this->createContact('cc1@example.test');
    $cc2 = $this->createContact('cc2@example.test');
    $bcc = $this->createContact('bcc@example.test');
    $this->saveData($reminderId, [$cc1, $cc2], [$bcc]);

    $params = $this->reminderMailParams($reminderId);
    scheduledccrecipients_civicrm_alterMailParams($params, 'messageTemplate');

    $this->assertEqualsCanonicalizing(['cc1@example.test', 'cc2@example.test'], explode(',', $params['cc']));
    $this->assertSame('bcc@example.test', $params['bcc']);
    $this->assertSame('to@example.test', $params['toEmail'], 'The main recipient is not changed');
  }

  public function testContactsWithoutEmailAreSkipped(): void {
    $reminderId = $this->createReminder();
    $this->saveData($reminderId, [$this->createContact(), $this->createContact('cc@example.test')], []);

    $params = $this->reminderMailParams($reminderId);
    scheduledccrecipients_civicrm_alterMailParams($params, 'messageTemplate');

    $this->assertSame('cc@example.test', $params['cc']);
    $this->assertSame('', $params['bcc']);
  }

  public function testOnlyTheRemindersOwnDataIsUsed(): void {
    $mine = $this->createReminder();
    $other = $this->createReminder();
    $this->saveData($mine, [$this->createContact('mine@example.test')], []);
    $this->saveData($other, [$this->createContact('other@example.test')], []);

    $params = $this->reminderMailParams($mine);
    scheduledccrecipients_civicrm_alterMailParams($params, 'messageTemplate');
    $this->assertSame('mine@example.test', $params['cc']);
  }

  public function testGetEmailsByContacts(): void {
    $a = $this->createContact('a@example.test');
    $b = $this->createContact('b@example.test');
    $this->assertSame([], getEmailsByContacts(NULL));
    $this->assertSame([], getEmailsByContacts(''));
    $this->assertEqualsCanonicalizing(['a@example.test', 'b@example.test'], getEmailsByContacts("$a,$b"));
  }

  private function saveData(int $reminderId, array $ccIds, array $bccIds): void {
    civicrm_api3('ScheduledReminderData', 'create', [
      'reminder_id' => $reminderId,
      'email_cc' => implode(',', $ccIds),
      'email_bcc' => implode(',', $bccIds),
    ]);
  }

}
