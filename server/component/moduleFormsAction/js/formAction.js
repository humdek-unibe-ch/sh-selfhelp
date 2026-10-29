// Forms only trigger actions when they are finished or deleted: hide the
// trigger types started and updated when the selected data table is a form.
$(document).ready(function () {
    var $editor = $('#formAction-editor');
    if ($editor.length === 0) {
        return;
    }
    // Compare ids as numbers: the ids are ZEROFILL columns, so the options of
    // the selects carry "0000000107" while the lists carry 107.
    var formTables = ($editor.data('form-tables') || []).map(Number);
    var blocked = ($editor.data('form-blocked-triggers') || []).map(Number);
    var $table = $('select[name="id_dataTables"]');
    var $trigger = $('select[name="id_formProjectActionTriggerTypes"]');

    function applyFormTriggers() {
        var isForm = formTables.indexOf(Number($table.val())) !== -1;
        $trigger.find('option').each(function () {
            if (blocked.indexOf(Number(this.value)) === -1) {
                return; // e.g. the empty placeholder keeps its own data-hidden
            }
            var hide = isForm;
            this.disabled = hide;
            this.hidden = hide;
            // bootstrap-select hides an option by data-hidden, not by the hidden property
            $(this).attr('data-hidden', hide ? 'true' : null);
        });
        var clear = isForm && blocked.indexOf(Number($trigger.val())) !== -1;
        if ($trigger.data('selectpicker')) {
            // rebuild the menu for the changed data-hidden, then clear through the picker
            $trigger.selectpicker('refresh');
            if (clear) {
                $trigger.selectpicker('val', '');
            }
        } else if (clear) {
            $trigger.val(''); // the picker is not built yet, it reads the value when it is
        }
    }

    $table.on('change', applyFormTriggers);
    applyFormTriggers();
});
