{literal}
<script type="text/javascript">
    CRM.$(function($) {
        const locationTypeGroup = $(`<tr id="emailLocationTypeGroup" class="crm-scheduleReminder-form-block-location_type_id">
          <td class="label">{/literal}{$form.location_type_id.label}</td>
          <td>{$form.location_type_id.html}{literal}</td>
        </tr>`);
        const emailCCGroup = $(`<tr id="emailCCGroup" class="crm-scheduleReminder-form-block-email_cc_field recipient">
          <td class="label">{/literal}{$form.email_cc.label}</td>
          <td>{$form.email_cc.html}{literal}</td>
        </tr>`);
        const emailBCCGroup = $(`<tr id="emailBCCGroup" class="crm-scheduleReminder-form-block-email_bcc_field recipient">
          <td class="label">{/literal}{$form.email_bcc.label}</td>
          <td>{$form.email_bcc.html}{literal}</td>
        </tr>`)
        const emailFromGroup = $('#from_email').closest('tr');
        // The location type belongs with the recipients: place it below the
        // recipients block (Limit or Add Recipients and the rows that depend on it).
        const recipientsGroup = $('tr.crm-scheduleReminder-form-block-recipient_group_id').last();
        locationTypeGroup.insertAfter(recipientsGroup.length ? recipientsGroup : emailFromGroup).show();
        emailCCGroup.insertAfter(emailFromGroup).show();
        emailBCCGroup.insertAfter(emailCCGroup).show();

        const fillReminderCustomFields = {/literal}{if $fill_reminder_custom_fields}{$fill_reminder_custom_fields}{else}0{/if}{literal};

        if (!$('#location_type_id').data('select2')) $('#location_type_id').crmSelect2({allowClear: true, placeholder: {/literal}'{ts escape='js'}- Primary email -{/ts}'{literal}});
        const locationTypeValue = '{/literal}{$location_type_value}{literal}';
        if (locationTypeValue) {
            $('#location_type_id').val(locationTypeValue).trigger('change');
        }

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
