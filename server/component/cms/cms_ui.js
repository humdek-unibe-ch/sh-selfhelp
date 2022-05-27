var collapsedProperties = false;
var unsavedChanges = false;
const RELATION_PAGE_FIELD = 'page_field';
const RELATION_SECTION_FIELD = 'section_field';
const RELATION_SECTION_CHILDREN = 'section_children';
const RELATION_PAGE_CHILDREN = 'page_children';
const RELATION_PAGE_NAV = 'page_nav';
const RELATION_SECTION_NAV = 'section_nav';
const RELATION_PAGE = 'page'; // used when we work with page columns/fields from the `page` table in the DB
const RELATION_SECTION = 'section';  // used when we work with section columns/fields from the `section` table in the DB

$(document).ready(function () {
    init_ui_cms();
});

//******************************************* JS UI for CMS ***********************************************

// Build custom javascript UI.
function init_ui_cms() {
    try {
        unsavedChanges = false;
        initEditToggle();
        $(window).scroll(function () {
            adjustPropertiesHeight();
        });
        initSaveProperties();
        initCollapseMenu();
        initCollapseProperties();
        $('.ui-select-picker').selectpicker();
        initChildrenArea();
        initUISectionsButtons();
        initSortableElements();
        adjustPropertiesHeight();
        initMarkdownFields(); // this function is in style textarea - textarea.js
        initCards();
        initConditionBuilder(); // this function is in style conditionBuilder - conditionBuilder.js
        initDataConfigBuilder(); // this function is in style dataConfigBuilder - dataConfigBuilder.js
        initJsonFields(); // this function is in style textarea - textarea.js
        $('select').selectpicker();
        if (collapsedProperties) {
            propertiesCollapse();
            propertiesCollapse();
        }
        initSaveBtn();
        initUnsavedChangesListener();
        initSmallButtons();
        initDeleteBtn();
        initExportBtn();
        initImport(); // CMS import import.js
        initImportBtn();
    } catch (error) {
        console.log(error);
        refresh_cms_ui();
    }
}

function initCards() {
    $('div.card-header.collapsible').on('click', function () {
        setTimeout(() => {
            $('.CodeMirror-sizer').each(function () {
                $(this).css('min-height', '32px'); // ugly hack for hidden fields to properly get height
            })
        }, 100);
        toggle_collapsible_card($(this)); // this function is in style cards card.js 

    });
}

// create a button add nee section above selected section
function addButtonNewSectionAbove(sectionData) {
    var icon = $('<i class="fas fa-plus-circle ui-section-btn ui-icon-button-white text-success" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Add new section above"></i>');
    $(icon).click(() => {
        var position = (sectionData.order_position * 10) - 5; // get the style position and insert above
        showAddSection(sectionData, true, position);
    })
    return icon;
}

// create a button add new section bellow the selected section
function addButtonNewSectionBelow(sectionData) {
    var icon = $('<i class="fas fa-plus-circle ui-section-btn ui-icon-button-white text-success" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Add new section below"></i>');
    $(icon).click(() => {
        var position = (sectionData.order_position * 10) + 5; // get the style position and insert bellow
        showAddSection(sectionData, true, position);
    })
    return icon;
}

// create a new button got to section
function addButtonGoToSection(sectionData) {
    var icon = $('<i class="fas fa-sign-in-alt ui-section-btn text-success" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Go to section: <code>' + sectionData['section_name'] + '</code>"></i>');
    $(icon).click(() => {
        window.location.replace(sectionData['go_to_section_url']);
    })
    return icon;
}

// create a new button add new child to selected section. Only sections witch can have children will have this button
function addButtonNewChild(sectionData) {
    var icon = $('<button type="button" class="btn btn-outline-success btn-sm m-auto ui-add-child" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Add new section"><span class="fas fa-plus"></span> Add new section</button>');
    $(icon).click(() => {
        sectionData['relation'] = RELATION_SECTION_CHILDREN; // this insert always in section
        showAddSection(sectionData, false, 0);
    })
    return icon;
}

// create a button remove the selected section
function addButtonRemoveSection(sectionData) {
    var icon = $('<i class="fas fa-minus-circle ui-section-btn text-danger ui-icon-button-white" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Remove the section"></i>');
    $(icon).click(() => {
        console.log(sectionData);
        confirmation('Do you really want to remove <code>' + sectionData['section_name'] + '</code>?', () => {
            removeSection(sectionData);
        });
    })
    return icon;
}

// create a new button for moving the section up
function addButtonMoveSectionUp(section) {
    var icon = $('<i class="fas fa-arrow-alt-circle-up ui-section-btn ui-icon-button-white text-primary" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Move the section up"></i>');
    $(icon).click(() => {
        moveSectionUp(section);
    })
    return icon;
}

// create a new button for moving the section down
function addButtonMoveSectionDown(section) {
    var icon = $('<i class="fas fa-arrow-alt-circle-down ui-section-btn ui-icon-button-white text-primary" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Move the section down"></i>');
    $(icon).click(() => {
        moveSectionDown(section);
    })
    return icon;
}

// add all UI buttons to the sections
function addUISectionButtons(section) {
    var sectionData = $(section).data('section');
    var buttonsHolder = $('<div class="ui-buttons-holder position-absolute justify-content-between"></div>');
    var buttonsHolderUpDown = $('<div class="ui-buttons-holder position-absolute justify-content-between"></div>');
    var buttonsHolderUpDownButtons = $('<div class="d-flex flex-column justify-content-between m-auto h-100"></div>');
    var buttonsHolderAdd = $('<div class="d-flex flex-column justify-content-between"></div>');
    $(buttonsHolderAdd).append(addButtonNewSectionAbove(sectionData));
    $(buttonsHolderAdd).append(addButtonGoToSection(sectionData));

    // add the new section button for sections without any child
    if (sectionData['can_have_children']) {
        var childrenHolder = $(section).find('.section-children-ui-cms').first();
        if ($(childrenHolder).children().length == 0) {
            $(childrenHolder).addClass('d-flex');
            $(childrenHolder).append(addButtonNewChild(sectionData));
        }
    }

    $(buttonsHolderAdd).append(addButtonNewSectionBelow(sectionData));
    $(buttonsHolder).append(buttonsHolderAdd);
    $(buttonsHolder).append(addButtonRemoveSection(sectionData));
    $(section).append(buttonsHolder);
    $(buttonsHolderUpDown).append(buttonsHolderUpDownButtons);
    $(buttonsHolderUpDownButtons).append(addButtonMoveSectionUp(section));
    $(buttonsHolderUpDownButtons).append(addButtonMoveSectionDown(section));
    $(section).append(buttonsHolderUpDown);

    $(section).off('click').on('click', function (e) {
        e.stopPropagation();
        // e.preventDefault();
        loadSectionFields(sectionData['go_to_section_url']);
    })
}

// confirmation function
// takes confirmation message and confirmCallback which is executed on confirmation
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

// execute ajax call
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

// load specific ids, sent in array
function update_new_data(data, elements, callback) {
    $('.popover').remove(); // first remove all tooltips if they are active
    elements.forEach(element => {
        $(element).empty().append($(data).find(element).children());
    });
    if (callback) {
        callback();
    }
    init_ui_cms(); // reload the UI initialization
    $('[data-toggle="popover"]').popover({ html: true }); // reload again the tooltips
}

// refresh the CMS_UI
function refresh_cms_ui(elements, callback) {
    console.log('refresh', elements);
    $.get(location.href, function (data) {
        update_new_data(data, elements, callback);
    });
}

// move section up if possible
function moveSectionUp(section) {
    section = $(section);
    var prev = section.prev();
    if (prev.length == 0)
        return;
    prev.css('z-index', 999).css('position', 'relative').animate({ top: section.height() }, 250);
    section.css('z-index', 1000).css('position', 'relative').animate({ top: '-' + prev.height() }, 300, function () {
        prev.css('z-index', '').css('top', '').css('position', '');
        section.css('z-index', '').css('top', '').css('position', '');
        section.insertBefore(prev);
        scrollToSection(section);
        reorderSectionsFromRoot($(section).data('section'), getChildrenOrder($(section).parent()));
    });
}

// scroll to the section only if the difference is higher than 500px
function scrollToSection(section) {
    if (Math.abs($(document).scrollTop() - $(section).offset().top) > 500) {
        $([document.documentElement, document.body]).animate({
            scrollTop: $(section).offset().top
        }, 1000);
    }
}

// move section down if possible
function moveSectionDown(section) {
    section = $(section);
    var next = section.next();
    if (next.length == 0)
        return;
    next.css('z-index', 999).css('position', 'relative').animate({ top: '-' + section.height() }, 250);
    section.css('z-index', 1000).css('position', 'relative').animate({ top: next.height() }, 300, function () {
        next.css('z-index', '').css('top', '').css('position', '');
        section.css('z-index', '').css('top', '').css('position', '');
        section.insertAfter(next);
        scrollToSection(section);
        reorderSectionsFromRoot($(section).data('section'), getChildrenOrder($(section).parent()));
    });
}

// add area for the sections with children
function addChildrenArea(sectionHolder) {
    var section = $(sectionHolder).children(":first")[0];
    // console.log(section);
    // var sectionHTML = $(section).html();
    // $(section).html('')
    // var childrenArea = $('<div class="section-children-ui-cms border rounded"></div>');
    // $(childrenArea).html(sectionHTML);  
    // $(section).append(childrenArea);  
    // $(section).wrapInner('<div class="section-children-ui-cms border rounded"></div>');
}

// init children area for the sections that can have children
function initChildrenArea() {
    var allSectionsWithChildren = $('.section-can-have-children');
    Array.from(allSectionsWithChildren).forEach((section) => {
        addChildrenArea(section);
    });
}

// init all section and add buttons to them
function initUISectionsButtons() {
    var allSections = $('.ui-section-holder');
    Array.from(allSections).forEach((section) => {
        addUISectionButtons(section);
    });
}

// return the children order based
function getChildrenOrder(parent) {
    var order = [];
    $(parent).children('.ui-section-holder').each(function (idx) {
        var section = this;
        var sectionData = $(section).data('section');
        order[sectionData['order_position']] = idx * 10;
    });
    return order.join();
}

// init all sections and make them sortable for faster reordering and re-arranging
function initSortableElements() {
    var sortableOptions = {
        scroll: true,
        bubbleScroll: true,
        // multiDrag: true, // Enable the plugin
        // multiDragKey : 'Control',
        // selectedClass: "bg-danger",
        group: "sections",
        fallbackOnBody: false,
        sort: true,
        animation: 150,
        swapThreshold: 0.65,
        ghostClass: 'drag-ghost',
        filter: ".ui-add-child",
        onEnd: function (evt) {
            try {
                if (evt.from == evt.to) {
                    // re-arrange
                    reorderSectionsFromRoot($(evt.item).data('section'), getChildrenOrder(evt.from));
                } else {
                    // move from one parent to another
                    console.log('Old parent', $(evt.item).data('section')['parent_id']);
                    var remove_data = Object.assign({}, $(evt.item).data('section'));
                    console.log(remove_data);
                    $(evt.to).children('.ui-section-holder').each(function (idx) {
                        // re-index the new group
                        prepareSectionInfo(this, idx);
                    });
                    console.log('New parent', $(evt.item).data('section')['parent_id'], $(evt.item).data('section'));
                    console.log("New section ", $(evt.item).data('section')['id_sections'], " should have position: ", $(evt.item).data('section')['order_position'] * 10);
                    console.log(getChildrenOrder(evt.to));
                    removeSection(remove_data, () => {
                        var sectionData = $(evt.item).data('section');
                        var sectionId = $(evt.item).data('section')['id_sections'];
                        var position = ($(evt.item).data('section')['order_position'] * 10) - 5;
                        sectionData['insert_sibling_section_url_modified'] = sectionData['insert_sibling_section_url'].replace(':parent_id', sectionData['parent_id'])
                        insertSection(sectionData, sectionId, true, position);
                    });

                }
            } catch (error) {
                console.log(error);
                refresh_cms_ui(['#ui-middle', '#properties']); // refresh the UI on error
            }
        }
    }

    // **************************** SECTION PAGE ***********************************
    var pageSections = $('#section-page-view > .card-body');
    Array.from(pageSections).forEach((sectionHolder) => {
        $(sectionHolder).children('.ui-section-holder').each(function (idx) {
            prepareSectionInfo(this, idx);
        });
        new Sortable(sectionHolder, sortableOptions);
    });
    // **************************** SECTION VIEW ***********************************
    var sectionSections = $('#section-section-view > .card-body');
    Array.from(sectionSections).forEach((section) => {
        $(section).children('.ui-section-holder').each(function (idx) {
            prepareSectionInfo(this, idx);
            $(this).find('>.ui-buttons-holder').slice(0, 2).addClass("d-none").removeClass('ui-buttons-holder'); // do not show the frame buttons when we are in specific section
        });
        new Sortable(section, sortableOptions);
    });
    // **************************** NESTED CHILDREN ***********************************
    var childrenSections = $('.section-children-ui-cms');
    Array.from(childrenSections).forEach((section) => {
        $(section).children('.ui-section-holder').each(function (idx) {
            prepareSectionInfo(this, idx);
        });
        new Sortable(section, sortableOptions);
        // loadNestedChildren(section, sortableOptions);
    });
}

function loadNestedChildren(children, sortableOptions) {
    var childrenSections = $(children).children('.section-can-have-children');
    Array.from(childrenSections).forEach((section) => {
        $(section).children('.ui-section-holder').each(function (idx) {
            console.log(this);
            prepareSectionInfo(this, idx);
        });
        new Sortable(section, sortableOptions);
        loadNestedChildren(section, sortableOptions);
    });
}

// prepare section info. Calculate parents data, adjust relations and urls
function prepareSectionInfo(section, idx) {
    var sectionData = $(section).data('section');
    sectionData['order_position'] = idx;
    var parents = $(section).parents('.ui-section-holder');
    var parent
    if (parents) {
        parent = parents[0];
    }
    parentData = $(parent).data('section');
    if (parentData) {
        sectionData['parent'] = "section";
        if (sectionData['update_section_url']) {
            sectionData['update_url'] = sectionData['update_section_url'].replace(':parent_id', parentData['id_sections']);
        }
        sectionData['relation'] = RELATION_SECTION_CHILDREN;
        sectionData['parent_id'] = parentData['id_sections'];
        if (sectionData['insert_sibling_section_url']) {
            sectionData['insert_sibling_section_url_modified'] = sectionData['insert_sibling_section_url'].replace(':parent_id', parentData['id_sections']);
        }
    } else {
        sectionData['update_url'] = sectionData['update_page_url']
        sectionData['relation'] = RELATION_PAGE_CHILDREN;
        sectionData['parent'] = "page";
        sectionData['parent_id'] = sectionData['id_pages'];
    }

    $(section).children('.badge').text(idx); // for debugging

    $(section).attr('data-section', sectionData);
    return sectionData;
}

// remove section from page or another section depending on the parameters
function removeSection(sectionData, callback) {
    executeAjaxCall(
        'post',
        sectionData['update_url'],
        {
            "remove-section-link": sectionData['id_sections'],
            "mode": "delete",
            "relation": sectionData['relation']
        },
        () => {
            console.log('deleted');
            if (callback) {
                callback();
            } else {
                refresh_cms_ui(['#ui-middle', '#properties']);
            }
        },
        () => {
            console.log('error');
            $.alert({
                title: 'Error!',
                content: 'The section was not deleted!',
            });
        });
}

// reorder section
function reorderSectionsFromRoot(sectionData, order) {
    console.log(order);
    executeAjaxCall(
        'post',
        sectionData['update_url'],
        {
            "mode": "update",
            "fields": {
                sections: {
                    1: {
                        1: {
                            id: "",
                            type: "section-list",
                            relation: sectionData['relation'],
                            content: order
                        }
                    }
                }
            }
        },
        () => {
            console.log('re-ordered');
            refresh_cms_ui(['#ui-middle', '#properties']);
        },
        () => {
            console.log('error');
            $.alert({
                title: 'Error!',
                content: 'The section was not re-ordered!',
            });
        });
}

// add already existing section
// sectionId - int - the id of the section which will be added
// sectionData - array - the section data with info how the section will be added
// addSibling - bool - if true we add a sibling we use the same parent if false we use the section as parent
// position - int - the position where it will be added 
function addSection(sectionId, sectionData, addSibling, position) {
    console.log('Add section', sectionId, sectionData, position);
    if (sectionId == sectionData['id_sections']) {
        $.alert({
            title: 'CMS UI',
            content: "It is not possible to insert a section inside the same section!"
        });
    } else {
        insertSection(sectionData, sectionId, addSibling, position);
    }
}

// add already existing section
// styleId - int - the id of the style which will be created as a new section
// sectionData - array - the section data with info how the section will be added
// addSibling - bool - if true we add a sibling we use the same parent if false we use the section as parent
// position - int - the position where it will be added 
function addNewSection(styleId, sectionData, addSibling, position, styleName) {
    console.log('Add new section', styleId, sectionData, position, styleName);
    createSection(sectionData, styleId, addSibling, position, styleName);
}

// show modal for add section
// sectionData - array - the section data with info how the section will be added
// position - int - the position where it will be added 
// addSibling - bool - if true we add a sibling we use the same parent if false we use the section as parent
function showAddSection(sectionData, addSibling, position) {
    $('#ui-add-section-modal').modal();
    $('#ui-add-section').css({ top: window.event.clientY - $('#ui-add-section').outerHeight() / 2, left: window.event.clientX + 10 });
    $('#nav-new-section-tab').tab("show"); //always show the first tab when modal is opened for consistency 

    $('#ui-new-section-btn').off('click').on('click', () => {
        if ($('#ui-new-section-select').val()) {
            var styleId = parseInt($('#ui-new-section-select').val());
            addNewSection(styleId, sectionData, addSibling, position, $('#ui-new-section-select  option:selected').text());
            $('#ui-add-section-modal').modal('hide');
        } else {
            $.alert({
                title: 'CMS UI',
                content: "Please select a style!"
            });
        }
    });

    $('#ui-unassigned-section-btn').off('click').on('click', () => {
        if ($('#ui-unassigned-section-select').val()) {
            var sectionId = parseInt($('#ui-unassigned-section-select').val());
            addSection(sectionId, sectionData, addSibling, position);
            $('#ui-add-section-modal').modal('hide');
        } else {
            $.alert({
                title: 'CMS UI',
                content: "Please select a section!"
            });
        }
    })

    $('#ui-reference-section-btn').off('click').on('click', () => {
        if ($('#ui-reference-section-select').val()) {
            var sectionId = parseInt($('#ui-reference-section-select').val());
            addSection(sectionId, sectionData, addSibling, position);
            $('#ui-add-section-modal').modal('hide');
        } else {
            $.alert({
                title: 'CMS UI',
                content: "Please select a section!"
            });
        }
    })

    // the import button
    $("#cmsImportJson").off('submit');
    $('#cmsImportJson').submit(function (e) {
        $('#ui-add-section-modal').modal('hide');
        e.preventDefault(); // avoid to execute the actual submit of the form.
        var form = $(this);
        var actionUrl = form.attr('action');
        var data = form.serializeArray();
        var parent_id = sectionData['id_sections'];
        if (addSibling) {
            parent_id = sectionData['parent_id'];
        }
        data.push({ name: 'parent_id', value: parent_id });
        data.push({ name: 'position', value: position });
        console.log(position);
        $.ajax({
            type: "POST",
            url: actionUrl,
            data: data, // serializes the form's elements.
            success: function (data) {
                // update_new_data(data, ['#ui-middle', '#section-ui-card-content>card-body', '#section-ui-card-properties>card-body', '#nav-menu']);
                refresh_cms_ui(['#ui-middle'], () => {
                    $($(data).find('[id^="section-controller-fail"]').get().reverse()).each(function(){
                        $('#ui-middle .sticky-top').prepend(this);
                    })
                    $($(data).find('[id^="section-controller-success"]').get().reverse()).each(function(){
                        $('#ui-middle .sticky-top').prepend(this);
                    })
                });
            }
        });

    });
}

// insert existing section
// addSibling - bool - if true we add a sibling we use the same parent if false we use the section as parent
function insertSection(sectionData, sectionId, addSibling, position) {
    executeAjaxCall(
        'post',
        getAddSectionUrl(sectionData, addSibling),
        {
            mode: "insert",
            relation: sectionData['relation'],
            "add-section-link": sectionId,
            position: position,
            ajax: true
        },
        () => {
            console.log('Section inserted');
            refresh_cms_ui(['#ui-middle', '#properties']);
        },
        () => {
            console.log('error');
            $.alert({
                title: 'Error!',
                content: 'The section was not inserted!',
            });
        });
}

// create new section
// addSibling - bool - if true we add a sibling we use the same parent if false we use the section as parent
function createSection(sectionData, styleId, addSibling, position, styleName) {
    var timestamp = Math.round((new Date()).getTime() / 1000);
    executeAjaxCall(
        'post',
        getAddSectionUrl(sectionData, addSibling),
        {
            mode: "insert",
            relation: sectionData['relation'],
            "add-section-link": "",
            "section-name-prefix": timestamp,
            "section-name": timestamp + '-' + styleName,
            // "section-name": '', // create without name
            "section-style": styleId,
            position: position
        },
        () => {
            console.log('Section inserted');
            refresh_cms_ui(['#ui-middle', '#properties']);
        },
        () => {
            console.log('error');
            $.alert({
                title: 'Error!',
                content: 'The section was not inserted!',
            });
        });
}

/**
 * get the url used for adding a section based on the parameters
 * 
 * @param {Array} sectionData 
 * the section data with info how the section will be added
 * @param {boolean} addSibling 
 * if true we add a sibling we use the same parent if false we use the section as parent
 * @returns {string}
 * return the url string
 */
function getAddSectionUrl(sectionData, addSibling) {
    var url = sectionData['insert_section_url'];
    if (addSibling) {
        if (sectionData["parent"] == 'page') {
            url = sectionData['insert_page_url'];
        } else {
            url = sectionData['insert_sibling_section_url_modified'];
        }
    }
    return url;
}


// add catcher and on error reload the ui
// block the UI until page is refreshed, otherwise we can get errors when we do fast changes
function initCollapseMenu() {

    $('[data-toggle=sidebar-collapse]').off('click');

    // Collapse/Expand icon
    $('#collapse-icon').addClass('fa-angle-double-left');

    // Collapse click
    $('[data-toggle=sidebar-collapse]').click(function () {
        sidebarCollapse();
    });
}

function sidebarCollapse() {
    $('.menu-collapsed').toggleClass('d-none');
    $('#sidebar-container').toggleClass('sidebar-expanded sidebar-collapsed');

    // Collapse/Expand icon
    $('#collapse-icon').toggleClass('fa-angle-double-left fa-angle-double-right');
}

function propertiesCollapse() {
    collapsedProperties = !collapsedProperties;
    $('.properties-collapsed').toggleClass('d-none');
    $('#properties').toggleClass('properties-expanded sidebar-collapsed');
    // Collapse/Expand icon
    $('#collapse-properties-icon').toggleClass('fa-angle-double-right fa-angle-double-left');
}

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

function initSaveProperties() {
    $('#save-properties').click((e) => {
        e.stopPropagation();
        console.log('Save properties');
    });
}

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
                data = $(data);
                var content_collapsed = $('#section-ui-card-content > .collapsed')[0];
                var properties_collapsed = $('#section-ui-card-properties > .collapsed')[0];
                if (!content_collapsed) {
                    // open content card
                    toggle_collapsible_card($(data).find('#section-ui-card-content > .card-header')); //function is defined in card.js
                }
                if (!properties_collapsed) {
                    // open properties card
                    toggle_collapsible_card($(data).find('#section-ui-card-properties > .card-header')); //function is defined in card.js
                }
                update_new_data(data, ["#ui-middle", '#section-ui-fields-holder']);
                history.pushState({}, null, toggleLink);
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

function getEditToggleLink() {
    var editLink = $('.ui-card-properties a:first').attr('href');
    // var cancelLink = $('.ui-card-properties > .card-body > form > a:first').attr('href');
    var cancelLink = location.href.replace('_update', '').replace('/update/prop', '');
    console.log(cancelLink);
    return editLink ? editLink : cancelLink;
}

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

    // without button
    // var usedSpace = $('.ui-card-properties')[0].getBoundingClientRect().top;
    // $('.ui-card-properties').first().css({ "height": "calc(100vh - " + usedSpace + "px)" });
    // console.log(usedSpace);

}

function initDeleteBtn() {
    $('#new-ui-delete').off('click').on('click', function () {
        console.log('delete');
        var delData = $('#new-ui-delete').data('data');
        console.log(delData);
        confirmation('<p>This will delete the page <code>' + delData['name'] + '</code> and all the data associated to this page.<p>Children elements are not affected.</p></p><p>You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, enter the name of the page.</p> <input id="deleteValue" type="text" class="form-control" >', () => {
            console.log($("#deleteValue").val());
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
                        if (refresh) {
                            refresh_cms_ui(['#ui-middle', '#properties']);
                        }
                    },
                    () => {
                        console.log('error');
                        $.alert({
                            title: 'Error!',
                            content: 'The ' + delData['relation'] + ' was not deleted!',
                        });
                    });
                if (delData['relation'] == RELATION_PAGE) {
                    location.href = delData['cms_url'];
                } else if (location.href.includes('/' + delData['id'] + '/')) {
                    // we want to delete from the section itself
                    // after deletion go to the page
                    location.href = delData['cms_url'];
                }
                if (redirect_url) {
                    location.href = redirect_url;
                }
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

function initExportBtn() {
    $('#new-ui-export').off('click').on('click', function (e) {
        e.preventDefault();
        console.log('export');
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

function initSaveBtn() {
    var saveForm = $('.ui-card-properties form').first()
    $(saveForm).off('submit');
    // btnSave.click(function (e) {
    //     e.preventDefault();
    //     var href = $(btnSave).attr('href');
    //     $(btnSave).attr('href', '#');
    //     e.stopPropagation();
    //     // $.redirectPost(href, { });
    //     console.log('save');
    // });

    $(saveForm).submit(function (e) {

        e.preventDefault(); // avoid to execute the actual submit of the form.
        if (!unsavedChanges) {
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
                update_new_data(data, ['#ui-middle', '#section-ui-card-content>card-body', '#section-ui-card-properties>card-body', '#nav-menu']);
            }
        });

    });
}

function initUnsavedChangesListener() {
    $(window).bind('beforeunload', function (e) {
        if (unsavedChanges) {
            return false;
        }
    });
    $('.ui-card-properties  :input').on('change', function () { //triggers change in all input fields including text type
        unsavedChanges = true;
    });

    $('.ui-card-properties textarea').on('change', function () { //triggers change in all textareas
        unsavedChanges = true;
    });
}

function initSmallButtons() {
    $(".style-section-cms-settings form > button").addClass("btn-sm");
    $(".ui-card-properties form > button").addClass("btn-sm");
}

function loadSectionFields(sectionUrl) {
    console.log(sectionUrl);
    executeAjaxCall(
        'get',
        sectionUrl,
        {},
        (data) => {
            data = $(data);
            // rework to keep the toggles as variable
            var content_collapsed = $('#section-ui-card-content > .collapsed')[0];
            var properties_collapsed = $('#section-ui-card-properties > .collapsed')[0];
            if (!content_collapsed && $('#section-ui-card-content')[0]) {
                // open content card
                toggle_collapsible_card($(data).find('#section-ui-card-content > .card-header')); //function is defined in card.js
            }
            if (!properties_collapsed) {
                // open properties card
                toggle_collapsible_card($(data).find('#section-ui-card-properties > .card-header')); //function is defined in card.js
            }
            update_new_data(data, ['#section-ui-fields-holder']);
            // history.pushState({}, null, sectionUrl);
        },
        () => {
            console.log('error');
            $.alert({
                title: 'Error!',
                content: 'Something went wrong!',
            });
        });
}

function initImportBtn() {

}
