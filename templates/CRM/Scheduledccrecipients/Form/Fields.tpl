{literal}
<script type="text/javascript">
    CRM.$(function($) {
        const emailCCGroup = $(`<tr id="emailCCGroup" class="crm-scheduleReminder-form-block-email_cc_field recipient">
          <td class="label">{/literal}{$form.email_cc.label}</td>
          <td>{$form.email_cc.html}{literal}</td>
        </tr>`);
        const emailBCCGroup = $(`<tr id="emailBCCGroup" class="crm-scheduleReminder-form-block-email_bcc_field recipient">
          <td class="label">{/literal}{$form.email_bcc.label}</td>
          <td>{$form.email_bcc.html}{literal}</td>
        </tr>`)
        const emailFromGroup = $('#from_email').closest('tr');
        emailCCGroup.insertAfter(emailFromGroup).show();
        emailBCCGroup.insertAfter(emailCCGroup).show();

        const fillReminderCustomFields = {/literal}{if $fill_reminder_custom_fields}{$fill_reminder_custom_fields}{else}0{/if}{literal};

        if(fillReminderCustomFields == 1) {
            let ccValues = [];
            let bccValues = [];
            {/literal}
            {foreach from=$cc_values  item=cc_value }ccValues.push({$cc_value});{/foreach}
            {foreach from=$bcc_values item=bcc_value}bccValues.push({$bcc_value});{/foreach}
            {literal}

            setTimeout(function() {
                setCustomData();
            }, 100);

            function setCustomData() {
                $('#email_cc').select2("val", ccValues);
                $('#email_bcc').select2("val", bccValues);
            }
        }
    });
</script>
{/literal}
