$(document).ready(function() {
    $('input[name="file"]').change(function() {
        var $label = $(this).next('.custom-file-label');
        $label.html($(this).val());
        $label.removeClass("text-muted");
        var fileName = $(this).val().replace('C:\\fakepath\\','');
        // fileName = removeFileExtension(fileName);
        $('#assetsFileName').val(fileName); //automatically fill the name; if the users want they can change it
    });
    $('#asset-upload-form').submit(function() {
        $(this).hide();
        $(this).prev().removeClass("d-none");
    });
});

function removeFileExtension(fileName) {
  return fileName.replace(/\.[^/.]+$/, ''); // Replace everything after the last dot (including the dot) with an empty string
}
