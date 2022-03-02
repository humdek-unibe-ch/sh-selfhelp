$(document).ready(function () {

    $('#defaultLanguage select').on('change', function () {
        var locale = $(this).val();
        console.log(locale);
        $.post(
            BASE_PATH + '/request/AjaxLanguage/ajax_set_user_language',
            { locale: locale},
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