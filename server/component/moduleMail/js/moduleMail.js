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

/* Formatting function for row details - modify as you need */
function format(mqid) {
    var columnNames = ['transaction_id', 'transaction_time', 'transaction_type', 'transaction_by', 'user_name', 'transaction_verbal_log']
    var transactions = JSON.parse($('#mailQueue').attr("data-transactions"));
    var html = '<table class = "ml-5 table-bordered"> <thead class="table-info"><tr>';
    for (var j = 0; j < columnNames.length; j++) {
        html = html + '<th scope="col">' + columnNames[j] + '</th>';
    }
    html = html + '</tr></thead> <tbody> ';
    for (var i = 0; i < transactions.length; i++) {
        if (transactions[i].id === mqid) {
            //this transaction belongs to this row;
            html = html + '<tr>'; //define row
            for (var j = 0; j < columnNames.length; j++) {
                //add cell values to the row
                html = html + '<td>' + transactions[i][columnNames[j]] + '</td>';
            }
            html = html + '</tr>'; //close row
        }
    }
    return html + '</tbody> </table>';
}

$(document).ready(function () {
    if (window.history.replaceState) {
        //prevent resned of the post ************ IMPORTANT *****************************
        window.history.replaceState(null, null, window.location.href);
    }

    $('select').selectpicker();

    if ($('textarea[name="body"]')[0]) {
        var simplemde = new SimpleMDE({
            autoDownloadFontAwesome: false,
            spellChecker: false,
            renderingConfig: {
                singleLineBreaks: false
            }
        });

        $('.style-section-body code').first().html(simplemde.options.previewRender($('.style-section-body code').first().html()));
    }

    var table = $('#mailQueue').DataTable({
        "order": [[0, "asc"]],
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel'
        ],
    });

    // Add event listener for opening and closing details
    $('#mailQueue tbody').on('click', 'td.details-control', function (e) {
        e.stopPropagation();
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            // Open this row
            row.child(format($(tr).attr("data-row-id"))).show();
            tr.addClass('shown');
        }
    });

    table.on('click', 'tr[id|="mailQueue-url"]', function (e) {
        var ids = $(this).attr('id').split('-');
        document.location = ids[2];
    });
    $(function () {
        $('[data-toggle="popover"]').popover({ html: true });
    });

    $('#dateFrom').flatpickr({
        dateFormat: 'd-m-Y',
        weekNumbers: true,
    });
    $('#btnFrom').on("click", function (e) {
        $('#dateFrom').focus();
    })

    $('#dateTo').flatpickr({
        dateFormat: 'd-m-Y',
        weekNumbers: true,
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

    //confirmation for run cronjob manually
    var runCronJobButton = $('.style-section-run_cron').first();
    runCronJobButton.click(function (e) {
        e.preventDefault();
        $.confirm({
            title: 'Check mail queue and send!',
            content: 'Are you sure that you want to manually run the cronjob? It will chekc for mails that should be sent and if there are some they will be sent.',
            buttons: {
                confirm: function () {
                    var href = $(runCronJobButton).attr('href');
                    $(runCronJobButton).attr('href', '#');
                    e.stopPropagation();
                    $.redirectPost(href, { mode: 'run_cron' });
                },
                cancel: function () {

                }
            }
        });
    });
});
