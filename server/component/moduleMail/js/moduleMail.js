$(document).ready(function () {
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
    $('#btnFrom').on("click", function(e){
        $('#dateFrom').focus();
    })

    $('#dateTo').datepicker({
        calendarWeeks: true,
        autoclose: true,
        todayHighlight: true,
        format: 'dd-mm-yyyy',
    });
    $('#btnTo').on("click", function(e){
        $('#dateTo').focus();
    })
});
