$(document).ready(function() {
    var table = $('#user-activity').DataTable({
        "order": [[1, "asc"]],
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel'
        ]
    });
    table.on('click', 'tr[id|="user-url"]', function(e) {
        var ids = $(this).attr('id').split('-');
        document.location = ids[2];
    });
    $(function () {
        $('[data-toggle="popover"]').popover({html:true});
    });
});
    