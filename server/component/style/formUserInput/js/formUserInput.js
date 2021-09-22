$(document).ready(function () {
    formSubmitEvent();
});

function formSubmitEvent() {
    $('form').on('submit', function (e) {
        if ($(this).find('input[name="ajax"]').val() == 1) {
            var is_log = $(this).find('input[name="is_log"]').val() == 1;
            var redirect_at_end = $(this).find('input[name="redirect_at_end"]').val();
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

            var form = this;
            // AJAX call
            $.ajax({
                type: 'post',
                url: $(this).attr('action'),
                data: $(this).serialize(),
                success: function (data) {
                    // Parse the page that is returned in order to get the alerts
                    var parser = new DOMParser();
                    var htmlDoc = parser.parseFromString(data, 'text/html');

                    // update inputs
                    $('.selfhelpInput').each(function () {
                        if ($(this).data('locked_after_submit') && $(this).val()) {
                            $(this).prop('disabled', true);
                        }
                    })
                    // update radios
                    $('.selfhelpRadio').each(function () {
                        if ($(this).data('locked_after_submit') && $('input[name="' + $(this).attr('name') + '"]:checked').val()) {
                            $(this).prop('disabled', true);
                        }
                    })
                    // update selects
                    $('.selfhelpSelect').each(function () {
                        console.log(this);
                        if ($(this).data('locked_after_submit') && $(this).val()) {
                            $(this).prop('disabled', true);
                        }
                    })

                    // assign success allerts
                    $(htmlDoc).find('.alert-success').each(function () {
                        $(this).insertBefore($(form)[0]);
                    });
                    //assign fail alerts
                    $(htmlDoc).find('.alert-danger').each(function () {
                        $(this).insertBefore($(form)[0]);
                    });

                    if (is_log) {
                        $(form)[0].reset();
                    }

                    // restore the original buttons labels
                    btnLabels.forEach(element => {
                        $(element.btn).html(element.origLabel);
                    });
                    if (redirect_at_end) {
                        window.location = redirect_at_end;
                    }
                }
            });
        }
    });
}
