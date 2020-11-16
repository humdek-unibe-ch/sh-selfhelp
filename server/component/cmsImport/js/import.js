$(document).ready(function () {
    $('input[name="file"]').change(function () {
        var $label = $(this).next('.custom-file-label');
        $label.html($(this).val());
        $label.removeClass("text-muted");
    });
    $('#asset-upload-form').submit(function () {
        $(this).hide();
        $(this).prev().removeClass("d-none");
    });

    const inputElement = document.getElementById("file");
    inputElement.addEventListener("change", handleFileSelect, false);
});

function handleFileSelect(evt) {
    var files = evt.target.files; // FileList object

    // use the 1st file from the list
    f = files[0];

    var reader = new FileReader();

    // Closure to capture the file information.
    reader.onload = (function (theFile) {
        return function (e) {
            try {
                var jsonFile = JSON.stringify(JSON.parse(e.target.result))
                $('#json').val(jsonFile);
            } catch (e) {
                $.alert({
                title: 'Error',
                content: e,
            });
            }
        };
    })(f);

    // Read in the image file as a data URL.
    reader.readAsText(f);
}