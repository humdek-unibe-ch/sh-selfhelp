$(document).ready(function () {
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

    var table = $('#qualtrics-project-stages').DataTable({
        "order": [[1, "asc"]]
    });
    table.on('click', 'tr[id|="stage-url"]', function (e) {
        var ids = $(this).attr('id').split('-');
        document.location = '../../stage/' + parseInt(ids[2]) + '/select/' + parseInt(ids[3]);
    });
    $(function () {
        $('[data-toggle="popover"]').popover({ html: true });
    });
});
