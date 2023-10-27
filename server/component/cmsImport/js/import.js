$(document).ready(function () {
    initImport();
});

function initImport() {
    $('input[name="file"]').off('change').change(function (e) {
        var $label = $(this).next('.custom-file-label');
        $label.html($(this).val());
        $label.removeClass("text-muted");
        handleFileSelect(e);
    });
    $('#asset-upload-form').off('submit').submit(function () {
        $(this).hide();
        $(this).prev().removeClass("d-none");
    });
}

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
                checkVersions(JSON.parse(e.target.result));
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

// check if the versions of the json are the same as these in the project
function checkVersions(json) {
    var alertTxt = '';
    var showAlert = false;
    $('#ui-import-section-btn').off('click');
    if ($('#dbVersion').val() != json['version']['database'] || $('#appVersion').val() != json['version']['application']) {
        showAlert = true;
        alertTxt = 'There are differences in the versions:<br>your db version - <b>' + $('#dbVersion').val() + '</b><br>json db version - <b>' + json['version']['database'] +
            '</b><br>your app version - <b>' + $('#appVersion').val() + '</b><br>' + 'json app version - <b>' + json['version']['application'] + '</b>';
    }
    if (showAlert) {
        var importBtn = $('#ui-import-section-btn');
        importBtn.click(function (e) {
            e.preventDefault();
            $.confirm({
                title: 'CMS Import',
                content: alertTxt,
                buttons: {
                    confirm: function () {
                        $('#cmsImportJson').submit();
                    },
                    cancel: function () {

                    }
                }
            });
        });
    }
}