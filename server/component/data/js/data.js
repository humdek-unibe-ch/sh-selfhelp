$(document).ready(function () {
    //************* all forms tables ******************************
    $('.adminData').each(function () {
        $(this).DataTable({
            scrollX: true,
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel'
            ],
            "columnDefs": [
                { "visible": false, "targets": 0 },
                { "visible": false, "targets": 1 }
            ]
        });
    });

    //************** Global filter ***********************************
    var dataFiller = $('.style-section-dataFilter input')[0];
    $(dataFiller).keyup(() => {
        $(".dataTables_filter .form-control").each(function () {
            $(this).val($(dataFiller).val()); // set the value in the search box
            $(this).trigger(jQuery.Event('keyup', { keycode: 13 })); //simulate key
        });
    })
});