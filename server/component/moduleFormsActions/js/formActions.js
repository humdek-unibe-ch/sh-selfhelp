$(document).ready(function () {
    if (window.history.replaceState) {
        //prevent resned of the post ************ IMPORTANT *****************************
        window.history.replaceState(null, null, window.location.href);
    }

    //datatable projects
    var table = $('#formActions').DataTable({
        "order": [[0, "asc"]],
        buttons:[
            {
                extend: 'searchBuilder',
                config: {
                    depthLimit: 2
                }
            }
        ],
        dom: 'Bfrtip',
    });

    table.on('click', 'tr[id|="formAction-url"]', function (e) {
        var ids = $(this).attr('id').split('-');
        document.location = 'formsActions/select/' + parseInt(ids[2]);
    });

    var actionOptions = {
        iconPrefix: 'fas fa-fw',
        classes: [],
        contextMenu: {
            enabled: true,
            isMulti: false,
            xoffset: -10,
            yoffset: -10,
            headerRenderer: function (rows) {
                if (rows.length > 1) {
                    // For when we have contextMenu.isMulti enabled and have more than 1 row selected
                    return rows.length + ' actions selected';
                } else if (rows.length > 0) {
                    let row = rows[0];
                    return 'Action ' + row[0] + ' selected';
                }
            },
        },
        showConfirmationMethod: (confirmation) => {
            $.confirm({
                title: confirmation.title,
                content: confirmation.content,
                buttons: {
                    confirm: function () {
                        return confirmation.callback(true);
                    },
                    cancel: function () {
                        return confirmation.callback(false);
                    }
                }
            });
        },
        buttonList: {
            enabled: true,
            iconOnly: false,
            containerSelector: '#my-button-container',
            groupClass: 'btn-group',
            disabledOpacity: 0.4,
            dividerSpacing: 10,
        },
        deselectAfterAction: false,
        items: [
            // Empty starter seperator to demonstrate that it won't render
            {
                type: 'divider',
            },

            {
                type: 'option',
                multi: false,
                title: 'View',
                iconClass: 'fa-eye',
                buttonClasses: ['btn', 'btn-outline-secondary'],
                contextMenuClasses: ['text-secondary'],
                action: function (row) {
                    var ids = row[0].DT_RowId.split('-');                    
                    window.open('formsActions/select/' + parseInt(ids[2]), '_blank')
                },
                isDisabled: function (row) {
                },
            },

            {
                type: 'divider',
            },

            {
                type: 'option',
                multi: false,
                title: 'Edit',
                iconClass: 'fa-edit',
                buttonClasses: ['btn', 'btn-outline-secondary'],
                contextMenuClasses: ['text-secondary'],
                action: function (row) {
                    var ids = row[0].DT_RowId.split('-');
                    window.open('formsActions/update/' + parseInt(ids[2]), '_blank')
                },
                isDisabled: function (row) {
                },
            },

            

            // Empty ending seperator to demonstrate that it won't render
            {
                type: 'divider',
            },
        ],
    };

    table.contextualActions(actionOptions);

});