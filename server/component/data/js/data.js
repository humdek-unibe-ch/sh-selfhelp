$(document).ready(function () {
    //************* all forms tables ******************************
    $('.adminData').each(function () {
        var table = $(this).DataTable({
            scrollX: true,
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel'
            ]
        });

        // hide all columns starting with "_"
        table.columns().every(function () {
            var column = this;
            var header = $(column.header()).text().trim();

            if (header.startsWith('_')) {
                column.visible(false);
            }
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