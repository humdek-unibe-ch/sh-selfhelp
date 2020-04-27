$(document).ready(function () {
    var table = $('#qualtrics-projects').DataTable({
        "order": [[0, "asc"]]
    });
    table.on('click', 'tr[id|="project-url"]', function (e) {
        var ids = $(this).attr('id').split('-');
        document.location = 'project/' + parseInt(ids[2]);
    });
    $(function () {
        $('[data-toggle="popover"]').popover({ html: true });
    });
});
