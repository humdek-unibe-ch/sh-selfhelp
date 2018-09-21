$(document).ready(function() {
    $('input[name="file"]').change(function() {
        var $label = $(this).next('.custom-file-label');
        $label.html($(this).val());
        $label.removeClass("text-muted");
    });
});
