
var formSubmitting = false;
var setFormSubmitting = function () { formSubmitting = true; };

$(document).ready(() => {
    $("#section-62").dirty();
    var options = {
        onDirty: function () {
            console.log('dirty');
        },
        onClean: function () {
            onCleanCalledCount++;
        },
        fireEventsOnEachChange: true,
        preventLeaving: true
    };
    $('.form-user-input').each(function () {
        $(this).dirty({
            options
        });

    });
    $('.form-user-input :input').each(function () {
        $(this).change(function () {
            $(this).addClass('form-user-input-dirty');
        });
    });

    $(window).bind('beforeunload', function (e) {

        if (formSubmitting || !isDirty()) {
            return undefined;
        }

        var confirmationMessage = 'It looks like you have been editing something. '
            + 'If you leave before saving, your changes will be lost.';

        (e || window.event).returnValue = confirmationMessage; //Gecko + IE
        return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.

    });

});



window.onload = function () {

};

function isDirty() {
    $('.form-user-input .form-user-input-dirty').each(function () {
        return true;
    });
    return false;
}