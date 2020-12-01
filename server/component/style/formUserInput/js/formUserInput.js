$(document).ready(function () {
    formSubmitEvent();
});

function formSubmitEvent() {
    $('form').on('submit', function (e) {
        if ($(this).find('input[name="ajax"]').val() == 1) {
            e.preventDefault(); //prevent default php submit

            $('.alert-danger').remove(); //remove previous fail messages if they exists
            $('.alert-success').remove(); //remove previous success messages if they exists

            // Add spinner to the buttons labels and keep the old text
            var btnLabels = [];
            $(this).find(':submit').each(function () {
                var origLabel = $(this).html();
                btnLabels.push(
                    {
                        btn: this,
                        origLabel: origLabel
                    }
                );
                $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"> </span> ' + origLabel);
            });
            
            var formId = $(this).attr('id');

            // AJAX call
            $.ajax({
                type: 'post',
                url: $('form').attr('action'),
                data: $('form').serialize(),
                success: function (data) {
                    // Parse the page that is returned in order to get the alerts
                    var parser = new DOMParser();
                    var htmlDoc = parser.parseFromString(data, 'text/html');

                    // assign success allerts
                    $(htmlDoc).find('.alert-success').each(function () {
                        $(this).insertBefore($('form'));
                    });
                    //assign fail alerts
                    $(htmlDoc).find('.alert-danger').each(function () {
                        $(this).insertBefore($('form'));
                    });
                                        
                    // assign form; if is log it will be cleared otherwise the values will be shown
                    $(htmlDoc).find('#' + formId).each(function () {
                        $('#' + formId).html($(this).html());
                    });

                    // restore the original buttons labels
                    btnLabels.forEach(element => {
                        $(element.btn).html(element.origLabel);
                    });
                }
            });
        }
    });
}
