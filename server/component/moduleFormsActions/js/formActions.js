$(document).ready(function () {
    if (window.history.replaceState) {
        //prevent resned of the post ************ IMPORTANT *****************************
        window.history.replaceState(null, null, window.location.href);
    }

    //datatable projects
    var table = $('#formActions').DataTable({
        "order": [[0, "asc"]]
    });
    table.on('click', 'tr[id|="formAction-url"]', function (e) {
        var ids = $(this).attr('id').split('-');
        document.location = 'formsActions/select/' + parseInt(ids[2]);
    });

});