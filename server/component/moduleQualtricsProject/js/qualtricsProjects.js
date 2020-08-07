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

    //datatable projects
    var table = $('#qualtrics-projects').DataTable({
        "order": [[0, "asc"]]
    });
    table.on('click', 'tr[id|="project-url"]', function (e) {
        var ids = $(this).attr('id').split('-');
        document.location = 'project/select/' + parseInt(ids[2]);
    });
    $(function () {
        $('[data-toggle="popover"]').popover({ html: true });
    });

    //datatable actions
    var table = $('#qualtrics-project-actions').DataTable({
        "order": [[1, "asc"]],
        "scrollX": true
    });
    table.on('click', 'tr[id|="action-url"]', function (e) {
        var ids = $(this).attr('id').split('-');
        if (document.location.href.includes('sync')) {
            document.location = '../action/' + parseInt(ids[2]) + '/select/' + parseInt(ids[3]);
        } else {
            document.location = '../../action/' + parseInt(ids[2]) + '/select/' + parseInt(ids[3]);
        }
    });
    $(function () {
        $('[data-toggle="popover"]').popover({ html: true });
    });

    //confirmation for Qualtrics sync
    var qualtricsSycnButton = $('.style-section-syncQualtricsSurveys').first();
    qualtricsSycnButton.click(function (e) {
        e.preventDefault();
        $.confirm({
            title: 'Qualtrics Synchronization',
            content: 'Are you sure that you want to synchonize all surveys added to this project in your Qualtrics account?',
            buttons: {
                confirm: function () {
                    var href = $(qualtricsSycnButton).attr('href');
                    $(qualtricsSycnButton).attr('href', '#');
                    event.stopPropagation();
                    $.redirectPost(href, { mode: 'select', type: 'qualtricsSync' });
                },
                cancel: function () {

                }
            }
        });
    });

});