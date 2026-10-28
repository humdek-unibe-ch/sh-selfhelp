$(document).ready(function () {
    initImport();
});

/**
 * Init the import forms: show the name of the selected file, read it and check its version.
 * The stand-alone import page posts its form. The forms of the CMS UI (cms_ui.js) are
 * submitted with ajax and use the helpers of this file too.
 */
function initImport() {
    $('form.cms-import-form input[name="file"]').off('change.cmsImport').on('change.cmsImport', function (e) {
        var file = this.files && this.files[0];
        var $label = $(this).next('.custom-file-label');
        $label.text(file ? file.name : 'Choose a JSON file').toggleClass("text-muted", !file);
        handleFileSelect(e);
    });
    $('#asset-upload-form').off('submit').submit(function () {
        $(this).hide();
        $(this).prev().removeClass("d-none");
    });
    // the stand-alone import page posts its form; ask for confirmation if the file comes from another version
    $('form.cms-import-form:not([data-ajax])').off('submit.cmsImport').on('submit.cmsImport', function (e) {
        var $form = $(this);
        if ($form.data('versionConfirmed')) {
            return;
        }
        e.preventDefault();
        if (!$form.find('input[name="json"]').val()) {
            $.alert({
                title: 'CMS Import',
                content: 'Please select a valid JSON file.',
            });
            return;
        }
        confirmImportVersion($form, function () {
            $form.data('versionConfirmed', true);
            $form.submit();
        });
    });
}

/**
 * Escape a text which is used inside html.
 * @param {any} text
 * @returns {String}
 */
function importEscapeHtml(text) {
    return $('<div>').text(text === undefined || text === null ? '' : text).html();
}

/**
 * Read the selected file, store it as string in the hidden `json` field of its form and check its versions.
 * After the file is read the event `cms-import:loaded` is triggered on the form with the parsed json.
 * @param {Event} evt the change event of the file input
 */
function handleFileSelect(evt) {
    var $form = $(evt.target).closest('form');
    var file = evt.target.files && evt.target.files[0];
    $form.data('versionMessage', null).data('importJson', null).data('versionConfirmed', false);
    $form.find('input[name="json"]').val('');
    if (!file) {
        return;
    }
    var reader = new FileReader();
    reader.onload = function (e) {
        try {
            var json = JSON.parse(e.target.result);
            $form.find('input[name="json"]').val(JSON.stringify(json));
            $form.data('importJson', json);
            checkVersions(json, $form);
            $form.trigger('cms-import:loaded', [json]);
        } catch (error) {
            $.alert({
                title: 'Error',
                content: 'The file is not a valid JSON file: ' + importEscapeHtml(error.message),
            });
        }
    };
    reader.readAsText(file);
}

/**
 * Check if the versions of the json are the same as these in the project. The warning is stored in the form.
 * @param {Object} json the parsed json file
 * @param {jQuery} $form the import form
 */
function checkVersions(json, $form) {
    var version = json['version'] || {};
    var dbVersion = $form.find('.cms-import-db-version').val();
    var appVersion = $form.find('.cms-import-app-version').val();
    var message = null;
    if (dbVersion != version['database'] || appVersion != version['application']) {
        message = 'There are differences in the versions:<br>your db version - <b>' + importEscapeHtml(dbVersion) + '</b><br>json db version - <b>' + importEscapeHtml(version['database']) +
            '</b><br>your app version - <b>' + importEscapeHtml(appVersion) + '</b><br>' + 'json app version - <b>' + importEscapeHtml(version['application']) + '</b>';
    }
    $form.data('versionMessage', message);
}

/**
 * Ask the user to confirm the import if the file was exported from another version.
 * @param {jQuery} $form the import form
 * @param {Function} proceed executed when the import can continue
 */
function confirmImportVersion($form, proceed) {
    var message = $form.data('versionMessage');
    if (!message) {
        proceed();
        return;
    }
    $.confirm({
        title: 'CMS Import',
        content: message,
        buttons: {
            confirm: function () {
                proceed();
            },
            cancel: function () {

            }
        }
    });
}
