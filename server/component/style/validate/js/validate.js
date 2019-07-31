$(document).ready(function() {
    var $cond_length = $('div.condition-length');
    var $cond_letter = $('div.condition-letter');
    var $cond_number = $('div.condition-number');
    $('input[name="pw"]').keyup(function () {
        var pw = $(this).val();
        if((pw.length >= 8) && pw.match(/[A-z]/) && pw.match(/\d/))
        {
            $('#pass_hint').html('match');
            this.setCustomValidity('');
        }
        else
        {
            $('#pass_hint').html('mismatch');
            this.setCustomValidity('The password must be at least 8 characters long and contain at least one letter and one number');
        }
    });
    $('input[name="pw_verify"]').keyup(function () {
        if($(this).val() === $('input[name="pw"]').val())
        {
            $('#pass_hint').html('match');
            this.setCustomValidity('');
        }
        else
        {
            $('#pass_hint').html('mismatch');
            this.setCustomValidity('The passwords must match');
        }
    });
});
