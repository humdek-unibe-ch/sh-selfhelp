$(document).ready(function () {
    formSubmitEvent();
});

/**
 * Initializes a form submission event handler for AJAX form submissions.
 * Prevents the default PHP form submission, sends an AJAX request to the form's action URL,
 * and updates the form and page elements based on the response.
 *
 * @function
 * @returns {void}
 */
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

                    // assign success alerts
                    $(htmlDoc).find('.alert-success').each(function () {
                        console.log($(this));
                        $(this).insertBefore($(form)[0]);
                    });
                    //assign fail alerts
                    $(htmlDoc).find('.alert-danger').each(function () {
                        $(this).insertBefore($(form)[0]);
                    });

                    // updateValues($('.selfHelp-form'), htmlDoc);                    

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

/**
 * After an AJAX call refresh the form inputs
 * @author Stefan Kodzhabashev
 * @date 2022-08-22
 * @param {any} elements
 * The elements that we want to refresh
 * @param {any} newData
 * The new data returned from the AJAX call
 * @returns {any}
 */
function updateValues(elements, newData) {
    $(elements).toArray().forEach(element => {
        $(element).attr('class').split(' ').forEach(className => {
            if (className.includes('style-section')) {
                var oldForm = $('.' + className);
                var newForm = $('.' + className, newData);
                if(newForm.length > 0){
                    oldForm.replaceWith(newForm);
                }
            }
        });
    });
}
