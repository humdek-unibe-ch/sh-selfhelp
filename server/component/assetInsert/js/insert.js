$(document).ready(function() {
    $('input[name="file"]').change(function() {
        var $label = $(this).next('.custom-file-label');
        $label.html($(this).val());
        $label.removeClass("text-muted");
        $('#assetsFileName').val($(this).val().replace('C:\\fakepath\\','')); //automatically fill the name; if the users want they can change it
    });
    $('#asset-upload-form').submit(function() {
        $(this).hide();
        $(this).prev().removeClass("d-none");
    });
});
