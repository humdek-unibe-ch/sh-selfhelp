$(document).ready(function () {
    initShowUserInput()
});

function initShowUserInput() {
    var $input = $('input[name="user_input_remove_id"]');
    $('.remove-user-input-field').off('click').click(function () {
        var ids = [];
        $(this).siblings('[id|="user-input-field"]').each(function () {
            var id = $(this).attr('id').split('-');
            ids.push(id[id.length - 1]);
        });
        $input.val(ids);
        var input_record_id = $('input[name="record_id"]');
        $input.val(ids);
        input_record_id.val(parseInt($(this).siblings('#user-input-field-record_id').text()));
    });

    $('.showUserInput').each(function () {
        if (!$(this).hasClass('dataTable')) {
            var classes = $(this).attr('class').split(' ');

            var dataTableConfig = { columnDefs: [] }; //init data table config

            dataTableConfig.columnDefs.push({ orderable: classes.includes('dt-sortable'), targets: '_all' }); //sortable
            dataTableConfig.searching = classes.includes('dt-searching'); //searching
            dataTableConfig.bPaginate = classes.includes('dt-bPaginate'); //bPaginate
            dataTableConfig.bInfo = classes.includes('dt-bInfo'); //bInfo

            dataTableConfig['scrollX'] = true;

            //check for ordered columns **********************************************************************************
            // dt-order-0-asc dt-order-1-desc
            var ordered = classes.filter(function (str) { return str.includes("dt-order"); });
            for (let i = 0; i < ordered.length; i++) {
                const ordClassElements = ordered[i].split('-');
                if (ordClassElements.length == 4) {
                    //correct order pattern                
                    if (ordClassElements[2] == parseInt(ordClassElements[2], 10) && (ordClassElements[3] === 'asc' || ordClassElements[3] === 'desc')) {
                        // check is 3 element number and 4 asc or desc
                        if (!dataTableConfig.order) {
                            //init order property
                            dataTableConfig.order = [];
                        }
                        dataTableConfig.order.push([ordClassElements[2], ordClassElements[3]])
                    }
                }
            }

            //check for hiiden columns **********************************************************************************
            // dt-hide-0
            var ordered = classes.filter(function (str) { return str.includes("dt-hide"); });
            for (let i = 0; i < ordered.length; i++) {
                const ordClassElements = ordered[i].split('-');
                if (ordClassElements.length == 3) {
                    //correct order pattern                
                    if (ordClassElements[2] == parseInt(ordClassElements[2], 10)) {
                        // check is 3 element number and 4 asc or desc
                        dataTableConfig.columnDefs.push({
                            "targets": [parseInt(ordClassElements[2], 10)],
                            "visible": false
                        })
                    }
                }
            }

            $(this).DataTable(dataTableConfig);
        }
    });
}
