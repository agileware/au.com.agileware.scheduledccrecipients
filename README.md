# Scheduled Reminder CC Recipients (au.com.agileware.scheduledccrecipients)

CiviCRM extension that adds extra email options to the **Schedule Reminders** form: **CC** and **BCC**
contacts, so a copy of a scheduled reminder can go to people who are not themselves recipients of the
reminder, and an **Email Location Type**, to choose which of a recipient's email addresses the
reminder is sent to.

This is useful, for example, if you want to notify an event coordinator or staff member whenever a
scheduled reminder email is sent to a Participant, Member, or other recipient, without adding that
person as a recipient of the reminder itself. Provides:

* An **Email Location Type** field, below the recipients, to send the reminder to a recipient's Work, Home or other email instead of their primary email
* CC and BCC contact fields on the Add and Edit Schedule Reminder form, placed after **From Email Address**
* Any number of CC and BCC contacts for each reminder
* The selected contacts' email addresses added to every email the reminder sends
* A `ScheduledReminderData` API (v3) to read or script the stored CC/BCC contacts
* No core patch, settings page or extra permission needed

This extension is licensed under [AGPL-3.0](https://github.com/agileware/au.com.agileware.scheduledccrecipients/blob/master/LICENSE.txt).

## Usage

1. Go to "Administer / Communications / Schedule Reminders" and add or edit a Scheduled Reminder.
2. The new fields appear on the form: **Email Location Type** directly below the recipients, and
   **CC** and **BCC** immediately after the **From Email Address** field. Select one or more CiviCRM
   contacts in CC and BCC.

   ![Scheduled Reminder CC and BCC fields, from an earlier version](https://github.com/agileware/au.com.agileware.scheduledccrecipients/raw/master/scheduledccrecipients.png)

3. Save the Scheduled Reminder as usual. The location type and the CC/BCC contacts are stored against
   that reminder.
4. When the Scheduled Reminder job runs and sends the reminder, the email goes to the chosen
   location type, and the CC and BCC contacts' email addresses are added to the outgoing message, in
   addition to the reminder's configured recipients.

### Email Location Type

By default a reminder goes to each recipient's primary email address, which is CiviCRM's behaviour.
Choose a location type, for example Work, to send it to the recipient's email of that type instead.

The primary email is still used when the recipient has no email of that type, when that email is on
hold, and when the reminder uses alternate recipients. CiviCRM only sends a reminder to recipients
who have a primary email address, so a recipient whose only email is of the chosen type is still
skipped.

### CC and BCC

A CC or BCC contact is included by their primary email address. A contact with no email address is
silently skipped. Only the Schedule Reminders emails are changed: all other CiviCRM mail, such as
contribution and event receipts, is left alone.

### API

CC/BCC selections are stored per reminder in the `civicrm_scheduledreminderdata` table, linked to the
Scheduled Reminder (`ActionSchedule`) by `reminder_id`. The contact ids are stored as a comma
separated list in `email_cc` and `email_bcc`, and the chosen location type in `location_type_id`.
The extension exposes a `ScheduledReminderData` API (v3) with `get`, `create` and `delete` actions,
for example:

    cv api3 ScheduledReminderData.get reminder_id=12

### Permissions

No extra permission is added. The new fields are part of the Schedule Reminders form, so they are
available to anyone who can use it (`administer CiviCRM`).

## Installation

1. Download the [latest version of this
   extension](https://github.com/agileware/au.com.agileware.scheduledccrecipients/archive/master.zip)
2. Extract it to your CiviCRM extensions directory, as defined in "System Settings / Directories".
3. Go to "Administer / System Settings / Extensions" and enable the "Scheduled Reminder CC
   Recipients (au.com.agileware.scheduledccrecipients)" extension.

No further configuration is required.

## Requirements

* CiviCRM 5.51 or greater, tested on CiviCRM 6.16.

## Upgrading

Earlier versions required a patch to CiviCRM core (`civcirm-core-reminder-tokens.patch`, which added a
`token_params` entry to the parameters passed to `hook_civicrm_alterMailParams`). The patch is no
longer needed and has been removed from this extension: CiviCRM already passes everything the
extension needs to recognise a scheduled reminder. If you applied it to your core, it is harmless and
can stay or be removed.

After updating, run the database upgrade ("Administer / System Settings / Extensions", or
`cv updb`). It widens the CC and BCC columns, which could previously hold only about four contacts,
and adds the location type column.

## Running the tests

The PHPUnit tests in `tests/phpunit` run inside a CiviCRM site, using CiviCRM's headless test
database. On a DDEV CiviCRM site with the extension installed, set the headless database DSN once
(see the `Configure cv's headless test database DSN` step of the shared
[ci-workflows](https://github.com/agileware/ci-workflows/blob/main/.github/workflows/civicrm-phpunit-tests.yml)
PHPUnit workflow), then run from the extension folder:

    phpunit9 --do-not-cache-result

The tests also run on pushes to `master` and on pull requests through `.github/workflows/phpunit-tests.yml`,
which calls that shared workflow.

# About the Authors

This CiviCRM extension was developed by the team at
[Agileware](https://agileware.com.au).

[Agileware](https://agileware.com.au) provide a range of CiviCRM services
including:

  * CiviCRM migration
  * CiviCRM integration
  * CiviCRM extension development
  * CiviCRM support
  * CiviCRM hosting
  * CiviCRM remote training services

Support your Australian [CiviCRM](https://civicrm.org) developers, [contact
Agileware](https://agileware.com.au/contact) today!

![Agileware](https://github.com/agileware/au.com.agileware.scheduledccrecipients/raw/master/logo/agileware-logo.png)
