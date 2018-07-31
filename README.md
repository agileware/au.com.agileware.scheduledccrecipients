# Scheduled Reminder Cc Recipients for CiviCRM #

This extension provides extra fields on the CiviCRM Scheduled reminder screen,
allowing you to send a carbon copy of the reminder to additional contacts.

This allows you to, for example, send a copy of a scheduled reminder to an event
coordinator when a participant signs up.

## Installation ##

1. Download [an archive](https://github.com/agileware/au.com.agileware.scheduledccrecipients/archive/master.zip)
   of the extension
2. Extract the contents of the extension archive into your CiviCRM Extensions
   Directory
3. Select the “Install” button from the Extensions page of your CiviCRM
   interface ( /civicrm/admin/extensions?reset=1 )


## Usage ##

When creating a new scheduled reminder, add additional contacts using the CC and
BCC fields that are added to this form:

![](scheduledccrecipients.png)

When the reminder is sent, these Contacts will be emailed in addition to the
Recipients configured for the reminder.
