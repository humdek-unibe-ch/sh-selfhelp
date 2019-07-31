$(document).ready(function() {
    $('input[name="file"]').change(function() {
        var $label = $(this).next('.custom-file-label');
        $label.html($(this).val());
        $label.removeClass("text-muted");
    });
    $('#asset-upload-form').submit(function() {
        $(this).hide();
        $(this).prev().removeClass("d-none");
    });
});
