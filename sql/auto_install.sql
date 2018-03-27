-- /*******************************************************
-- *
-- * civicrm_scheduledreminderdata
-- *
-- * Table to store custom data of Scheduled Reminders
-- *
-- *******************************************************/
CREATE TABLE `civicrm_scheduledreminderdata` (
  `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique ScheduledReminderData ID',
  `reminder_id` int unsigned    COMMENT 'FK to Scheduled Reminder',
  `email_cc` varchar(32)    COMMENT 'Email CC field of Schedule reminder',
  `email_bcc` varchar(32)    COMMENT 'Email BCC field of Schedule reminder',
  PRIMARY KEY ( `id` ),CONSTRAINT FK_civicrm_scheduledreminderdata_reminder_id FOREIGN KEY (`reminder_id`) REFERENCES `civicrm_action_schedule`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;