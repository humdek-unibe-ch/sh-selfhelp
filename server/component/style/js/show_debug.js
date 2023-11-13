$(document).ready(function () {
    $('.data-debug').each(function () {
        var debug_data = $(this).data('debug');
        console.log(debug_data['field']['section_name']);
        console.log(debug_data);
        $(this).removeData('debug').removeAttr('data-debug');
    });
});
