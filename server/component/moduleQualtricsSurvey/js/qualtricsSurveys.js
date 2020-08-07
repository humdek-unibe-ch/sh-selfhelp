$(document).ready(function () {
    var table = $('#qualtrics-surveys').DataTable({
        "order": [[0, "asc"]]
    });
    table.on('click', 'tr[id|="survey-url"]', function (e) {
        var ids = $(this).attr('id').split('-');
        document.location = 'survey/select/' + parseInt(ids[2]);
    });
    $(function () {
        $('[data-toggle="popover"]').popover({ html: true });
    });
});
