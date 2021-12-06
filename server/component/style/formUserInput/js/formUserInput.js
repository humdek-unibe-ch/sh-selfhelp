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

                    // update inputs - the function is in the style js
                    check_input_locked_after_submit();
                    // update radios - the function is in the style js
                    check_radio_locked_after_submit();
                    // update selects - the function is in the style js
                    check_select_locked_after_submit();
                    // update sliders - the function is in the style js
                    check_slider_locked_after_submit();
                    // update textarea - the function is in the style js
                    check_textarea_locked_after_submit();

                    // get the form class which includes the sectionId, this is the way we will find it from the returned html and replace it
                    // var searchClasses = [];
                    // $(form).attr('class').trim().split(' ').forEach(element => {
                    //     searchClasses.push('.'+element);
                    // });
                    // console.log(searchClasses);
                    // $(htmlDoc).find(searchClasses.join(',')).each(function () {
                    //     // update all children inside the form with the new data
                    //     $(form).html($(this).html());
                    // });

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
