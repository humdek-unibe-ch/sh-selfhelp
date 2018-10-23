$(document).ready(function() {
    $('input.form-check-input').change(function() {
        $hidden = $(this).prev();
        $('input[name="' + $hidden.prop('name') + '"]').val("");
        if($(this).prop('checked'))
            $hidden.val("checked");
    });
});
