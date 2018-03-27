<table>
    <tr id="emailCCGroup" class="crm-scheduleReminder-form-block-email_cc_field recipient" style="display: none;">
        <td class="label">{$form.email_cc.label}</td>
        <td>{$form.email_cc.html}</td>
    </tr>

    <tr id="emailBCCGroup" class="crm-scheduleReminder-form-block-email_bcc_field recipient" style="display: none;">
        <td class="label">{$form.email_bcc.label}</td>
        <td>{$form.email_bcc.html}</td>
    </tr>
</table>


{literal}
<script type="text/javascript">
    CRM.$(function($) {
        $('#emailBCCGroup').insertAfter('#recipientList').show();
        $('#emailCCGroup').insertAfter('#recipientList').show();

        var fillReminderCustomFields = {/literal}{if $fill_reminder_custom_fields}{$fill_reminder_custom_fields}{else}0{/if}{literal};

        if(fillReminderCustomFields == 1) {
            var ccValues = [];
            var bccValues = [];
            {/literal}
                {foreach from=$cc_values item=cc_value}
                    {literal}
                        ccValues.push({/literal}{$cc_value}{literal});
                    {/literal}
                {/foreach}

                {foreach from=$bcc_values item=bcc_value}
                    {literal}
                        bccValues.push({/literal}{$bcc_value}{literal});
                    {/literal}
                {/foreach}

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