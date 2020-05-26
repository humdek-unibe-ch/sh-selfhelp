// jquery extend function for post submit
$.extend(
    {
        redirectPost: function (location, args) {
            var form = '';
            $.each(args, function (key, value) {
                value = value.split('"').join('\"')
                form += '<input type="hidden" name="' + key + '" value="' + value + '">';
            });
            $('<form action="' + location + '" method="POST">' + form + '</form>').appendTo($(document.body)).submit();
        }
    });

$(document).ready(function () {
    $('select').selectpicker();

    var table = $('#mailQueue').DataTable({
        "order": [[0, "asc"]]
    });
    table.on('click', 'tr[id|="mailQueue-url"]', function (e) {
        var ids = $(this).attr('id').split('-');
        document.location = ids[2];
    });
    $(function () {
        $('[data-toggle="popover"]').popover({ html: true });
    });

    $('#dateFrom').datepicker({
        calendarWeeks: true,
        autoclose: true,
        todayHighlight: true,
        format: 'dd-mm-yyyy',
    });
    $('#btnFrom').on("click", function (e) {
        $('#dateFrom').focus();
    })

    $('#dateTo').datepicker({
        calendarWeeks: true,
        autoclose: true,
        todayHighlight: true,
        format: 'dd-mm-yyyy',
    });
    $('#btnTo').on("click", function (e) {
        $('#dateTo').focus();
    })

    //confirmation for send/resend mail queueu
    var qualtricsSycnButton = $('.style-section-send').first();
    qualtricsSycnButton.click(function () {
        if (confirm("Are you sure that you want to send this mail right now?")) {
            var href = $(qualtricsSycnButton).attr('href');
            $(qualtricsSycnButton).attr('href', '#');
            event.stopPropagation();
            $.redirectPost(href, { mode: 'send' });
        }
    });

    //confirmation for delete mail queueu
    var qualtricsSycnButton = $('.style-section-delete').first();
    qualtricsSycnButton.click(function () {
        if (confirm("Are you sure that you want to delete this mail queue?")) {
            var href = $(qualtricsSycnButton).attr('href');
            $(qualtricsSycnButton).attr('href', '#');
            event.stopPropagation();
            $.redirectPost(href, { mode: 'delete' });
        }
    });
});
