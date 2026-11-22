<?php
use CRM_Scheduledccrecipients_ExtensionUtil as E;

return [
  'name' => 'ScheduledReminderData',
  'table' => 'civicrm_scheduledreminderdata',
  'class' => 'CRM_Scheduledccrecipients_DAO_ScheduledReminderData',
  'getInfo' => fn() => [
    'title' => E::ts('Scheduled Reminder Data'),
    'title_plural' => E::ts('Scheduled Reminder Datas'),
    'description' => E::ts('Table to store custom data of Scheduled Reminders'),
    'log' => TRUE,
    'add' => '4.6',
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique ScheduledReminderData ID'),
      'add' => '4.4',
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'reminder_id' => [
      'title' => E::ts('Reminder ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to Scheduled Reminder'),
      'add' => '4.6',
      'entity_reference' => [
        'entity' => 'ActionSchedule',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'email_cc' => [
      'title' => E::ts('Email Cc'),
      'sql_type' => 'text',
      'input_type' => 'Text',
      'description' => E::ts('Email CC field of Schedule reminder'),
      'add' => '4.6',
    ],
    'email_bcc' => [
      'title' => E::ts('Email Bcc'),
      'sql_type' => 'text',
      'input_type' => 'Text',
      'description' => E::ts('Email BCC field of Schedule reminder'),
      'add' => '4.6',
    ],
    'location_type_id' => [
      'title' => E::ts('Email Location Type'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Select',
      'description' => E::ts('Location type of the email address the reminder is sent to. NULL means the primary email.'),
      'add' => '1.6',
      'pseudoconstant' => [
        'table' => 'civicrm_location_type',
        'key_column' => 'id',
        'label_column' => 'display_name',
      ],
    ],
  ],
];
