$(document).ready(function () {

    $('#defaultLanguage select').on('change', function () {
        var id_languages = $(this).val();
        console.log(id_languages);
        $.post(
            BASE_PATH + '/request/AjaxLanguage/ajax_set_user_language',
            { id_languages: id_languages},
            function (data) {
                if (data.success) {
                    location.reload();
                }
                else {
                    console.log(data);
                }
            },
            'json'
        );
    });

});