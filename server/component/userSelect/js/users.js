$(document).ready(function() {
    $('#user-activity').DataTable({
        "order": [[1, "asc"]]
    });
    $('tr[id|="user-url"]').click(function(e) {
        var ids = $(this).attr('id').split('-');
        document.location = ids[2];
    });
    $(function () {
        $('[data-toggle="popover"]').popover({html:true});
    });
});
