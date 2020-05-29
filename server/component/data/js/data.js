$(document).ready(function () {
    //************* all forms tables ******************************
    $('.adminData').each(function () {
        $(this).DataTable({
            "scrollX": true,
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf'
            ],
            "columnDefs": [
                { "visible": false, "targets": 0 },
                { "visible": false, "targets": 1 }
            ]
        });
    });

    //************** User table ***********************************
    var table = $('#user-activity').DataTable({
        "order": [[1, "asc"]]
    });
    table.on('click', 'tr[id|="user-url"]', function (e) {
        var ids = $(this).attr('id').split('-');
        document.location = ids[2].replace('/user/', '/data/');
    });
    $(function () {
        $('[data-toggle="popover"]').popover({ html: true });
    });

    //************** Reset button ***********************************
    $('#btnReset').click(function () {
        if (window.location.pathname.substr(window.location.pathname.length - 4) === 'data') {
            //just reload page
            location.reload();
        } else {
            window.location.href = '../data';
        }
    })

    //************** Global filter ***********************************
    $('#dataFilter').keyup(() => {
        $(".dataTables_filter .form-control").each(function () {
            $(this).val($('#dataFilter').val()); // set the value in the search box
            $(this).trigger(jQuery.Event('keyup', { keycode: 13 })); //simulate key
        });
    })

});