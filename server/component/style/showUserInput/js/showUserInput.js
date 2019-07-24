$(document).ready(function() {
    var $input = $('input[name="user_input_remove_id"]');
    $('.remove-user-input-field').click(function() {
        var ids = [];
        $(this).siblings('[id|="user-input-field"]').each(function() {
            var id = $(this).attr('id').split('-');
            ids.push(id[id.length - 1]);
        });
        $input.val(ids);
    });
});
