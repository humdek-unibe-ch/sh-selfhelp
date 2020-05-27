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
    if (window.history.replaceState) {
        //prevent resned of the post ************ IMPORTANT *****************************
        window.history.replaceState(null, null, window.location.href);
    }

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
    var sendMailQueueButton = $('.style-section-send').first();
    sendMailQueueButton.click(function (e) {

        e.preventDefault();

        $.confirm({
            title: 'Send Mail Queueu!',
            content: 'Are you sure that you want to send this mail right now?',
            buttons: {
                confirm: function () {
                    var href = $(sendMailQueueButton).attr('href');
                    $(sendMailQueueButton).attr('href', '#');
                    e.stopPropagation();
                    $.redirectPost(href, { mode: 'send' });
                },
                cancel: function () {

                }
            }
        });
    });

    //confirmation for delete mail queueu
    var deleteMailQueueButton = $('.style-section-delete').first();
    deleteMailQueueButton.click(function (e) {

        e.preventDefault();

        $.confirm({
            title: 'Delete Mail Queueu!',
            content: 'Are you sure that you want to delete this mail queue?',
            buttons: {
                confirm: function () {
                    var href = $(deleteMailQueueButton).attr('href');
                    $(deleteMailQueueButton).attr('href', '#');
                    e.stopPropagation();
                    $.redirectPost(href, { mode: 'delete' });
                },
                cancel: function () {

                }
            }
        });
    });
});
