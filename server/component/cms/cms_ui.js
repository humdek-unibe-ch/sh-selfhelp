var collapsedProperties = false;
var unsavedChanges = [];
const RELATION_PAGE_FIELD = 'page_field';
const RELATION_SECTION_FIELD = 'section_field';
const RELATION_SECTION_CHILDREN = 'section_children';
const RELATION_PAGE_CHILDREN = 'page_children';
const RELATION_PAGE_NAV = 'page_nav';
const RELATION_SECTION_NAV = 'section_nav';
const RELATION_PAGE = 'page'; // used when we work with page columns/fields from the `page` table in the DB
const RELATION_SECTION = 'section';  // used when we work with section columns/fields from the `section` table in the DB
const DEBUG = false;

$(document).ready(function () {
    init_ui_cms();
});

//******************************************* JS UI for CMS ***********************************************

// Build custom javascript UI.
function init_ui_cms() {
    try {
        unsavedChanges = [];
        initEditToggle();
        $(window).scroll(function () {
            adjustPropertiesHeight();
        });
        initSaveProperties();
        initCollapseMenu();
        initCollapseProperties();
        $('.ui-select-picker').selectpicker();
        initOutline(); // in cms_outline.js
        initFieldSearch(); // in cms_outline.js
        adjustPropertiesHeight();
        $('select').selectpicker();
        initEntryListSelectedColumnsSync();
        if (collapsedProperties) {
            propertiesCollapse();
            propertiesCollapse();
        }
        initSaveBtn();
        initUnsavedChangesListener();
        initSmallButtons();
        initDeleteBtn();
        initExportBtn();
        initSortableNavElements();
        loadCustomCSSClasses();
        initRemoveNavButtons();
        initPageOrder(); // in cms.js
        initStyles();
        initDescriptionItem();
    } catch (error) {
        console.log(error);
        refresh_cms_ui();
    }
}



/**
 * Load dynamically all init functions form the styles that have init functions
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
function initStyles() {
    // load dynamically all init functions form the styles that have init functions
    var styles = $("#properties").data('styles');
    if (styles) {
        styles.forEach((style) => {
            var styleName = style['name'].substr(0, 1).toUpperCase() + style['name'].substr(1);
            if (window['init' + styleName]) {
                window['init' + styleName]();
            }
        })
    }
}

/**
 * Keep `entryList.selected_columns` synced with the selected `data_table`.
 * Loads columns on init and reloads them when the table changes.
 */
function initEntryListSelectedColumnsSync() {
    var $form = $('.ui-card-properties form').first();
    if ($form.length === 0) {
        return;
    }

    $form.find('select.entryList-data-table-select').each(function () {
        var $dataTableSelect = $(this);
        var dataTableName = $dataTableSelect.attr('name') || '';
        var match = dataTableName.match(/^fields\[data_table\]\[([^\]]+)\]\[([^\]]+)\]\[content\]$/);
        if (!match) {
            return;
        }

        var targetName = 'fields[selected_columns][' + match[1] + '][' + match[2] + '][content][]';
        var $columnsSelect = $form.find('select.entryList-selected-columns-select').filter(function () {
            return $(this).attr('name') === targetName;
        }).first();

        if ($columnsSelect.length === 0) {
            return;
        }

        var loadColumns = function () {
            var tableId = $dataTableSelect.val();
            var selectedValues = $columnsSelect.val() || [];
            if (!tableId) {
                $columnsSelect.empty();
                $columnsSelect.selectpicker('refresh');
                return;
            }

            $.post(
                BASE_PATH + '/request/AjaxDataSource/get_table_fields',
                { id: tableId, exclude_system: 1 },
                function (data) {
                    if (!data.success) {
                        return;
                    }

                    var columns = [];
                    try {
                        columns = JSON.parse(data.data);
                    } catch (error) {
                        console.log("Error while parsing", data.data);
                        return;
                    }

                    $columnsSelect.empty();
                    var nextSelected = [];
                    columns.forEach(function (columnName) {
                        var isSelected = selectedValues.indexOf(columnName) !== -1;
                        if (isSelected) {
                            nextSelected.push(columnName);
                        }
                        $columnsSelect.append(
                            $('<option></option>')
                                .attr('value', columnName)
                                .prop('selected', isSelected)
                                .text(columnName)
                        );
                    });
                    $columnsSelect.val(nextSelected);
                    $columnsSelect.selectpicker('refresh');
                    $columnsSelect.trigger('change');
                },
                'json'
            );
        };

        $dataTableSelect.off('changed.bs.select.entryListColumns').on('changed.bs.select.entryListColumns', function () {
            loadColumns();
        });

        loadColumns();
    });
}

/**
 * confirmation function
 * takes confirmation message and confirmCallback which is executed on confirmation
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} content
 * @param {Function} confirmCallback
 * @param {any} type
 * @returns {any}
 */
function confirmation(content, confirmCallback, type) {
    $.confirm({
        title: 'CMS UI',
        content: content,
        type: type,
        buttons: {
            confirm: function () {
                confirmCallback();
            },
            cancel: function () {

            }
        }
    });
}

/**
 * execute ajax call
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {string} method // post or get
 * @param {string} url
 * @param {any} data
 * @param {Function} callbackSuccess
 * @param {Function} callbackError
 * @returns {any}
 */
function executeAjaxCall(method, url, data, callbackSuccess, callbackError) {
    jQuery.ajax({
        url: url,
        method: method,
        data: data,
        async: true,
        cache: false,
        dataType: "html",
        success: function (data) {
            if (data) {
                callbackSuccess(data);
            }
            else {
                callbackError();
            }
        },
        error: function (e) {
            console.log(e);
            callbackError();
        }
    });
}

/**
 * load specific ids, sent in array
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} data
 * @param {Array} elements
 * @param {Function} callback executed after the elements are replaced and before the UI is initialized
 * @param {Function} afterInit executed after the UI is initialized
 * @returns {any}
 */
function update_new_data(data, elements, callback, afterInit) {
    if (!elements) {
        return;
    }
    if (elements.indexOf("#multiple-users-warning") === -1 && elements.indexOf("#ui-middle") === -1 && elements.indexOf("#sticky-top") === -1) {
        // check if the parents are not there 
        elements.push("#multiple-users-warning");
    }
    $('.popover').remove(); // first remove all tooltips if they are active
    elements.forEach(element => {
        $(element).empty().append($(data).find(element).children());
    });
    if (callback) {
        callback();
    }
    init_ui_cms(); // reload the UI initialization
    $('[data-toggle="popover"]').popover({
        html: true,
        placement: 'top'
    }); // reload again the tooltips
    if (afterInit) {
        afterInit();
    }
}

/**
 * refresh the CMS_UI
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {Array} elements
 * @param {Function} callback
 * @returns {any}
 */
function refresh_cms_ui(elements, callback) {
    $.get(location.href, function (data) {
        update_new_data(data, elements, callback);
    });
}

/**
 * add catcher and on error reload the ui
 * block the UI until page is refreshed, otherwise we can get errors when we do fast changes
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
function initCollapseMenu() {

    $('[data-toggle=sidebar-collapse]').off('click');

    // Collapse/Expand icon
    $('#collapse-icon').addClass('fa-angle-double-left');

    // Collapse click
    $('[data-toggle=sidebar-collapse]').click(function () {
        sidebarCollapse();
    });
}

/**
 * Collapse sidebar
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
function sidebarCollapse() {
    $('.menu-collapsed').toggleClass('d-none');
    $('#sidebar-container').toggleClass('sidebar-expanded sidebar-collapsed');

    // Collapse/Expand icon
    $('#collapse-icon').toggleClass('fa-angle-double-left fa-angle-double-right');
}

/**
 * Collapse properties
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
function propertiesCollapse() {
    collapsedProperties = !collapsedProperties;
    $('.properties-collapsed').toggleClass('d-none');
    $('#properties').toggleClass('properties-expanded sidebar-collapsed');
    // Collapse/Expand icon
    $('#collapse-properties-icon').toggleClass('fa-angle-double-right fa-angle-double-left');
}

/**
 * Init collapse properties
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
function initCollapseProperties() {
    // Collapse/Expand icon
    $('[data-toggle=properties-collapse]').off('click');
    if (collapsedProperties) {
        $('#collapse-properties-icon').addClass('fa-angle-double-left');
        $('.properties-collapsed').addClass('d-none');
        $('#properties').addClass('sidebar-collapsed');
    } else {
        $('#collapse-properties-icon').addClass('fa-angle-double-right');
    }
    // Collapse click
    $('[data-toggle=properties-collapse]').click(function () {
        propertiesCollapse();
    });
}

/**
 * Init save properties button
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
function initSaveProperties() {
    $('#save-properties').click((e) => {
        e.stopPropagation();
    });
}

/**
 * Init edit toggle button
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
function initEditToggle() {
    $('#ui-edit-toggle').off('change');
    var editLink = $('.ui-card-properties a:first').attr('href')
    if (editLink) {
        // we are in view mode -> edit link exists
        $('#ui-edit-toggle').bootstrapToggle('off');
    } else {
        // we are in edit mode
        $('#ui-edit-toggle').bootstrapToggle('on');
    }
    var toggleLink = getEditToggleLink();
    $('#ui-edit-toggle').change(function () {
        executeAjaxCall(
            'get',
            toggleLink,
            {},
            (data) => {
                history.pushState({ href: toggleLink }, null, toggleLink);
                data = $(data);
                var cardState = outlineCaptureCardState(); // in cms_outline.js: the cards stay open or closed
                update_new_data(data, ["#ui-middle", '#section-ui-fields-holder', "#section-ui-page-list", '#section-ui-global-page-list', "#section-ui-navigation-hierarchy-list"], null, () => {
                    outlineApplyCardState(cardState);
                });
            },
            () => {
                console.log('error');
                $.alert({
                    title: 'Error!',
                    content: 'Something went wrong!',
                });
            });
    })
}

/**
 * Get edit toggle link
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {String}
 */
function getEditToggleLink() {
    var editLink = $('.ui-card-properties a:first').attr('href');
    var cancelLink = location.href.replace('_update', '').replace('/update/prop', '');
    return editLink ? editLink : cancelLink;
}

/**
 * Adjust properties height
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
function adjustPropertiesHeight() {
    // with button down
    var saveBtnHeight = $('.ui-card-properties form > button').first().outerHeight();
    var uiCardProperties = $('.ui-card-properties');
    if (uiCardProperties && saveBtnHeight) {
        var usedSpace = uiCardProperties[0].getBoundingClientRect().top + (saveBtnHeight ? saveBtnHeight : 0);
        if (saveBtnHeight) {
            $('.ui-card-properties').first().css({ "height": "calc(100vh - " + usedSpace + "px - 1rem)" });
        } else {
            $('.ui-card-properties').first().css({ "height": "calc(100vh - " + usedSpace + "px)" });
        }
    }
}

/**
 * Init delete button
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
function initDeleteBtn() {
    $('#new-ui-delete').off('click').on('click', function () {
        var delData = $('#new-ui-delete').data('data');
        confirmation('<p>This will delete <code>' + delData['name'] + '</code> and all the data associated to it.<p>Children elements are not affected.</p></p><p>You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, enter the keyword of the page.</p> <input id="deleteValue" type="text" class="form-control" >', () => {
            if ($("#deleteValue").val() == delData['name']) {
                var redirect_url = null;
                var refresh = false;
                if (delData['relation'] == RELATION_PAGE) {
                    redirect_url = delData['cms_url'];
                } else if (location.href.includes('/' + delData['id'] + '/')) {
                    // we want to delete from the section itself
                    // after deletion go to the page
                    redirect_url = delData['cms_url'];
                } else {
                    refresh = true;
                }
                executeAjaxCall(
                    'post',
                    delData['del_url'],
                    {
                        "name": delData['name']
                    },
                    () => {
                        if (redirect_url) {
                            location.href = redirect_url;
                        } else if (delData['relation'] == RELATION_PAGE) {
                            location.href = delData['cms_url'];
                        } else if (location.href.includes('/' + delData['id'] + '/')) {
                            // we want to delete from the section itself
                            // after deletion go to the page
                            location.href = delData['cms_url'];
                        } else if (refresh) {
                            location.reload();
                        }
                    },
                    () => {
                        $.alert({
                            title: 'Error!',
                            content: 'The ' + delData['relation'] + ' was not deleted!',
                        });
                    });
            } else {
                $.alert({
                    title: 'CMS UI',
                    type: "red",
                    content: 'Failed to delete the page: The verification text does not match with the page keyword.',
                });
            }
        }, "red");
    })
}

/**
 * Init export button
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
function initExportBtn() {
    $('#new-ui-export').off('click').on('click', function (e) {
        e.preventDefault();
        executeAjaxCall(
            'post',
            $('#new-ui-export').attr('href'),
            {},
            (data) => {
                const jsonExportData = $(data).find('#jsonExportData').val();
                try {
                    var originalData = JSON.parse(jsonExportData);
                    const a = document.createElement("a");
                    a.href = URL.createObjectURL(new Blob([JSON.stringify(originalData, null, 2)], {
                        type: "text/plain"
                    }));
                    a.setAttribute("download", originalData['file_name'] + ".json");
                    $(a).addClass('d-none');
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                } catch (error) {
                    $.alert({
                        title: 'Error!',
                        content: 'Error while exporting!',
                    });
                }
            },
            () => {
                console.log('error');
                $.alert({
                    title: 'Error!',
                    content: 'Error while exporting!',
                });
            });
    })
}

/**
 * Init save button
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
function initSaveBtn() {
    var saveForm = $('.ui-card-properties form').first()
    $(saveForm).off('submit');

    $(saveForm).submit(function (e) {

        e.preventDefault(); // avoid to execute the actual submit of the form.
        if (unsavedChanges.length == 0) {
            // if there is no changes do not try to save
            return;
        }

        var form = $(this);
        var actionUrl = form.attr('action');
        $.ajax({
            type: "POST",
            url: window.location.href,
            data: form.serialize(), // serializes the form's elements.
            success: function (data) {
                var cardState = outlineCaptureCardState(OUTLINE_SIDEBAR_CARDS); // the menus of the sidebar are replaced, keep them as the user left them
                update_new_data(data, ['#ui-middle', '#section-ui-card-content>card-body', '#section-ui-card-properties>card-body', '#nav-menu',
                    "#section-ui-navigation-hierarchy-list", "#header-position", ".style-section-page-order-wrapper", "#section-ui-page-list", '#section-ui-global-page-list', "#cms-alerts"],
                    null, function () {
                        outlineApplyCardState(cardState);
                    });
            }
        });

    });
}

/**
 * Init unsaved changes listener 
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
function initUnsavedChangesListener() {
    $(window).bind('beforeunload', function (e) {
        if (unsavedChanges.length > 0) {
            return false;
        }
    });
    $('.ui-card-properties  :input').not('.ui-field-search-input').on('change', function () { //triggers change in all input fields including text type (the search field is not a property)
        unsavedChanges.push(this);
    });

    $('.ui-card-properties textarea').on('change', function () { //triggers change in all textareas
        unsavedChanges.push(this);
    });
}

/**
 * Init small buttons
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
function initSmallButtons() {
    $(".style-section-cms-settings form > button").addClass("btn-sm");
    $(".ui-card-properties form > button").addClass("btn-sm");
}

/**
 * Init sortable nav elements
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
function initSortableNavElements() {
    $('.children-list.sortable').each(function (idx) {
        var $input = $(this).prev();
        var $list = $(this);
        $list.sortable("destroy");
        $list.children('li').each(function (idx) {
            $(this).children('.badge').text(idx); //reset badge values used for reordering
        });
        $list.sortable({
            animation: 150,
            onSort: function (evt) {
                var order = [];
                $list.children('li').each(function (idx) {
                    order[$(this).children('.badge').text()] = idx * 10;
                });
                $input.val(order);
            },
            onUpdate: function () {
                unsavedChanges.push(this);
            }
        });
    });
}

/**
 * Load custom css classes
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
function loadCustomCSSClasses() {
    $('.ui-children-list > a').removeClass('btn-secondary').addClass('btn-sm btn-primary rounded-top');
}

/**
 * Init remove nav buttons
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
function initRemoveNavButtons() {
    $('.ui-children-list > li > a').off('click').on('click', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var url_params = url.split('/');
        var url_params = url_params.slice(-3);
        var listElement = $(this).parent();
        confirmation('Do you really want to remove <code>' + $(listElement).find('span').eq(1).html() + '</code>?', () => {
            executeAjaxCall(
                'post',
                url,
                {
                    "remove-section-link": url_params[2],
                    "mode": url_params[0],
                    "relation": url_params[1]
                },
                (data) => {
                    $(listElement).remove();
                    refresh_cms_ui(['#section-ui-navigation-hierarchy-list', '#section-ui-page-list', '#section-ui-global-page-list']);
                },
                () => {
                    console.log('error');
                    $.alert({
                        title: 'Error!',
                        content: 'The section was not deleted!',
                    });
                });
        });

    })
}

