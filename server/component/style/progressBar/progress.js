$(document).ready(function() {
    $('.progress-bar').each(function() {
        $(this).width($(this).attr('aria-valuenow') + '%');
    });
});
