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
        var simplemde = new EasyMDE({
            autoDownloadFontAwesome: false,
            spellChecker: false,
            renderingConfig: {
                singleLineBreaks: false
            }
        });

        $('.style-section-body code').first().html(simplemde.options.previewRender($('.style-section-body code').first().html()));
    }

    var table = $('#mailQueue').DataTable({
        order: [[2, "asc"]],
        dom: 'QBfrtip',
        buttons: ['copy', 'csv', 'excel'],
        columnDefs: [{
            orderable: false,
            className: 'select-checkbox',
            targets: 0,
            width: "20px"
        }, {
            targets: 'no-sort',
            orderable: false
        }],
        select: {
            style: 'multi',
            selector: 'td:first-child'
        },
        searchBuilder: {
            columns: [3, 4, 8, 9, 10, 11]
        },
    });

    var actionOptions = {
        iconPrefix: 'fas fa-fw',
        classes: [],
        contextMenu: {
            enabled: true,
            isMulti: true,
            xoffset: -10,
            yoffset: -10,
            headerRenderer: function (rows) {
                if (rows.length > 1) {
                    // For when we have contextMenu.isMulti enabled and have more than 1 row selected
                    return rows.length + ' jobs selected';
                } else if (rows.length > 0) {
                    let row = rows[0];
                    return 'Job ' + row[2] + ' selected';
                }
            },
        },
        showConfirmationMethod: (confirmation) => {
            $.confirm({
                title: confirmation.title,
                content: confirmation.content,
                buttons: {
                    confirm: function () {
                        return confirmation.callback(true);
                    },
                    cancel: function () {
                        return confirmation.callback(false);
                    }
                }
            });
        },
        buttonList: {
            enabled: true,
            iconOnly: false,
            containerSelector: '#my-button-container',
            groupClass: 'btn-group',
            disabledOpacity: 0.4,
            dividerSpacing: 10,
        },
        deselectAfterAction: true,
        items: [
            // Empty starter seperator to demonstrate that it won't render
            {
                type: 'divider',
            },

            {
                type: 'option',
                multi: false,
                title: 'View',
                iconClass: 'fa-eye',
                buttonClasses: ['btn', 'btn-outline-secondary'],
                contextMenuClasses: ['text-secondary'],
                action: function (row) {
                    console.log(row);
                    var ids = row[0].DT_RowId.split('-');
                    window.open(ids[2], '_blank')
                },
                isDisabled: function (row) {
                },
            },

            {
                type: 'divider',
            },

            {
                type: 'option',
                title: 'Execute Job',
                multi: true,
                multiTitle: 'Execute Jobs',
                iconClass: 'fa-play',
                buttonClasses: ['btn', 'btn-outline-danger'],
                contextMenuClasses: ['text-danger'],

                isDisabled: (row) => {
                    return row.role === '';
                },

                confirmation: function (rows) {
                    return {
                        title: 'Execute Job!',
                        content: rows.length > 1 ? 'Are you sure that you want to execute these jobs right now?' : 'Are you sure that you want to execute this job right now?'
                    };

                },
                action: function (rows) {
                    var executedRows = 0;
                    rows.forEach(row => {
                        var url = row.DT_RowId.split('-')[2];
                        $.post(url, { mode: 'execute' }, function (result) {
                            executedRows++;
                            console.log(executedRows);
                            if (executedRows == rows.length) {
                                // all rows executed, refresh
                                $('#btn-search-scheduled-jobs').click();
                            }
                        });
                    });
                },
            },

            {
                type: 'option',
                title: 'Delete Job',
                multi: true,
                multiTitle: 'Delete Jobs',
                iconClass: 'fa-trash',
                buttonClasses: ['btn', 'btn-outline-danger'],
                contextMenuClasses: ['text-danger'],

                isDisabled: (row) => {
                    return row.role === '';
                },

                isDisabledStrictMode: true,

                confirmation: function (rows) {
                    return {
                        title: 'Delete Job!',
                        content: rows.length > 1 ? 'Are you sure that you want to delete these jobs right now?' : 'Are you sure that you want to delete this job right now?'
                    };

                },

                action: function (rows) {
                    var executedRows = 0;
                    rows.forEach(row => {
                        var url = row.DT_RowId.split('-')[2];
                        $.post(url, { mode: 'delete' }, function (result) {
                            executedRows++;
                            console.log(executedRows);
                            if (executedRows == rows.length) {
                                // all rows executed, refresh
                                $('#btn-search-scheduled-jobs').click();
                            }
                        });
                    });
                },
            },

            // Empty ending seperator to demonstrate that it won't render
            {
                type: 'divider',
            },
        ],
    };

    table.contextualActions(actionOptions);

    // Add event listener for opening and closing details
    $('#mailQueue tbody').on('click', 'td.details-control', function (e) {
        e.stopPropagation();
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        } else {
            // Open this row
            row.child(format($(tr).attr("data-row-id"))).show();
            tr.addClass('shown');
        }
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
    var sendMailQueueButton = $('.style-section-execute').first();
    sendMailQueueButton.click(function (e) {
        e.preventDefault();
        $.confirm({
            title: 'Execute Job!',
            content: 'Are you sure that you want to execute this job right now?',
            buttons: {
                confirm: function () {
                    var href = $(sendMailQueueButton).attr('href');
                    $(sendMailQueueButton).attr('href', '#');
                    e.stopPropagation();
                    $.redirectPost(href, { mode: 'execute' });
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
            title: 'Delete Scheduled Jobs!',
            content: 'Are you sure that you want to delete this job?',
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
            title: 'Check Scheduled Jobs queue and send!',
            content: 'Are you sure that you want to manually run the cronjob? It will chekc for scheduled jobs that should be executed and if there are some they will be executed.',
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
