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
        initUISectionsButtons();
        initSortableElements();
        adjustPropertiesHeight();
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
        initSortableNavElements();
        loadCustomCSSClasses();
        initRemoveNavButtons();
        initPageOrder(); // in cms.js
        initStyles();
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
 * create a button add new section above selected section
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} sectionData
 * @returns {any}
 */
function addButtonNewSectionAbove(sectionData) {
    var icon = $('<i class="fas fa-plus-circle ui-section-btn ui-icon-button-white text-success" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Add new section above"></i>');
    $(icon).click(() => {
        var position = (sectionData.order_position * 10) - 5; // get the style position and insert above
        showAddSection(sectionData, true, position);
    })
    return icon;
}

/**
 * create a button add new section bellow the selected section
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} sectionData
 * @returns {any}
 */
function addButtonNewSectionBelow(sectionData) {
    var icon = $('<i class="fas fa-plus-circle ui-section-btn ui-icon-button-white text-success" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Add new section below"></i>');
    $(icon).click(() => {
        var position = (sectionData.order_position * 10) + 5; // get the style position and insert bellow
        showAddSection(sectionData, true, position);
    })
    return icon;
}

/**
 * create a new button got to section
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} sectionData
 * @returns {any}
 */
function addButtonGoToSection(sectionData) {
    var icon = $('<i class="fas fa-sign-in-alt ui-section-btn text-success" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Go to section: <code>' + sectionData['section_name'] + '</code>"></i>');
    $(icon).click(() => {
        window.location.replace(sectionData['go_to_section_url']);
    })
    return icon;
}

/**
 * create a new button add new child to selected section. Only sections witch can have children will have this button
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} sectionData
 * @returns {any}
 */
function addButtonNewChild(sectionData) {
    var icon = $('<button type="button" class="ui-add-first-child btn btn-outline-success btn-sm m-auto ui-add-child" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Add new section"><span class="fas fa-plus"></span> Add new section</button>');
    $(icon).click(() => {
        showAddSection(sectionData, false, 0);
    })
    return icon;
}

/**
 * create a button remove the selected section
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} sectionData
 * @returns {any}
 */
function addButtonRemoveSection(sectionData) {
    var icon = $('<i class="fas fa-minus-circle ui-section-btn text-danger ui-icon-button-white" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Remove the section"></i>');
    $(icon).click(() => {
        confirmation('Do you really want to remove <code>' + sectionData['section_name'] + '</code>?', () => {
            removeSection(sectionData);
        });
    })
    return icon;
}

/**
 * create a new button for moving the section up
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} section
 * @returns {any}
 */
function addButtonMoveSectionUp(section) {
    var icon = $('<i class="fas fa-arrow-alt-circle-up ui-section-btn ui-icon-button-white text-primary" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Move the section up"></i>');
    $(icon).click(() => {
        moveSectionUp(section);
    })
    return icon;
}

/**
 * create a new button for moving the section down
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} section
 * @returns {any}
 */
function addButtonMoveSectionDown(section) {
    var icon = $('<i class="fas fa-arrow-alt-circle-down ui-section-btn ui-icon-button-white text-primary" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Move the section down"></i>');
    $(icon).click(() => {
        moveSectionDown(section);
    })
    return icon;
}

/**
 * add all UI buttons to the sections    
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} section
 * @param {boolean} hideUIButtons
 * @returns {any}
 */
function addUISectionButtons(section, hideUIButtons) {
    var sectionData = $(section).data('section');
    var buttonsHolder = $('<div class="ui-buttons-holder position-absolute justify-content-between"></div>');
    var buttonsMenuHolder = $('<div class="ml-5  d-flex flex-column justify-content-between"> </div>');
    var buttonsHolderUpDown = $('<div class="ui-buttons-holder position-absolute justify-content-between"></div>');
    var buttonsHolderUpDownButtons = $('<div class="d-flex flex-column justify-content-between m-auto h-100"></div>');
    var buttonsHolderAdd = $('<div class="d-flex flex-column justify-content-between"></div>');
    $(buttonsHolderAdd).append(addButtonNewSectionAbove(sectionData));

    // add the new section button for sections without any child
    if (sectionData['can_have_children']) {
        var childrenHolder = $(section).find('.section-children-ui-cms').first();
        if (sectionData['children'] == 0) {
            $(childrenHolder).addClass('d-flex rounded ui-dotted-border');
            var newSectionData = { ...sectionData };
            if ($(section).hasClass('ui-section-holder-page')) {
                newSectionData['relation'] = RELATION_PAGE_CHILDREN; // this insert always in page
            } else {
                newSectionData['relation'] = RELATION_SECTION_CHILDREN; // this insert always in section
            }
            $(childrenHolder).find('.ui-add-first-child').remove();
            $(childrenHolder).append(addButtonNewChild(newSectionData));
        }
    }

    $(buttonsHolderAdd).append(addButtonNewSectionBelow(sectionData));
    $(buttonsHolder).append(buttonsHolderAdd);
    $(buttonsHolder).append(addButtonRemoveSection(sectionData));
    $(buttonsHolderUpDown).append(buttonsHolderUpDownButtons);
    $(buttonsHolderUpDownButtons).append(addButtonMoveSectionUp(section));
    $(buttonsMenuHolder).append(addMenu(section, sectionData));
    if ($(section).height() > 500) {
        // add menu down too. It is a big section
        $(buttonsMenuHolder).append(addMenu(section, sectionData));
    }
    $(buttonsHolderUpDownButtons).append(addButtonMoveSectionDown(section));
    $(section).append($('<div class="ui-buttons-holder position-absolute justify-content-between"></div>').append(buttonsMenuHolder));
    if (!hideUIButtons) {
        $(section).append(buttonsHolder);
        $(section).append(buttonsHolderUpDown);
    }
}

/**
 * Add the menu
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} section
 * @param {any} sectionData
 * @returns {any}
 */
function addMenu(section, sectionData) {
    var menu = $('<div class="d-flex bg-white mt-1 mb-1 p-1 rounded ui-menu-holder"></div>');
    $(menu).append(addBtnShowSectionFields(section, sectionData))
    $(menu).append(addBtnGoToSection(sectionData))
    return menu
}

/**
 * Add button show section fields
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} section
 * @param {any} sectionData
 * @returns {any}
 */
function addBtnShowSectionFields(section, sectionData) {
    var icon = $('<i class="fas fa-eye ui-menu-btn text-primary mr-2 ml-2" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Show Section Fields"></i>');
    $(icon).click(() => {
        // mark the selected section
        $(".ui-marked-section").removeClass("ui-marked-section");
        loadSectionFields(sectionData['go_to_section_url'], () => {
            setTimeout(() => {
                $(section).addClass('ui-marked-section');
                $(section).find('.ui-menu-holder').addClass('ui-marked-section');
            }, 0);
        });
    })
    return icon;
}

/**
 * Add button go to section
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} sectionData
 * @returns {any}
 */
function addBtnGoToSection(sectionData) {
    var icon = $('<i class="far fa-object-group ui-menu-btn text-primary mr-2 ml-2" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Go To Section <code>' + sectionData['section_name'] + '</code>"></i>');
    $(icon).click(() => {
        // load the selected section. The view will be from the section afterthat
        window.location.replace(sectionData['go_to_section_url']);
    })
    return icon;
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
 * @param {Function} callback
 * @returns {any}
 */
function update_new_data(data, elements, callback) {
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
 * move section up if possible
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} section
 * @returns {any}
 */
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

/**
 * scroll to the section only if the difference is higher than 500px
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} section
 * @returns {any}
 */
function scrollToSection(section) {
    if (Math.abs($(document).scrollTop() - $(section).offset().top) > 500) {
        $([document.documentElement, document.body]).animate({
            scrollTop: $(section).offset().top
        }, 1000);
    }
}

/**
 * move section down if possible
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} section
 * @returns {any}
 */
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

/**
 * init all section and add buttons to them
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
function initUISectionsButtons() {
    var allSections = $('.ui-section-holder');
    var sectionIdx = 0;
    Array.from(allSections).forEach((section) => {
        var hideUIButtons = false;
        var sectionData = $(section).data('section');
        if (!sectionData['id_sections']) {
            // it is a page           
            hideUIButtons = true;
        } else if (sectionData['params']['sid'] == sectionData['id_sections']) {
            hideUIButtons = true;
        }
        addUISectionButtons(section, hideUIButtons);
        sectionIdx++;
    });
}

/**
 * return the children order based
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} parent
 * @returns {any}
 */
function getChildrenOrder(parent) {
    var order = [];
    $(parent).children('.ui-section-holder').each(function (idx) {
        var section = this;
        var sectionData = $(section).data('section');
        order[sectionData['order_position']] = idx * 10;
    });
    return order.join();
}

/**
 * init all sections and make them sortable for faster reordering and re-arranging
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
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
        swapThreshold: 0.85,
        ghostClass: 'drag-ghost',
        filter: ".ui-add-child",
        direction: 'vertical',
        onEnd: function (evt) {
            try {
                if (evt.from == evt.to) {
                    // re-arrange
                    reorderSectionsFromRoot($(evt.item).data('section'), getChildrenOrder(evt.from));
                } else {
                    // move from one parent to another
                    var remove_data = Object.assign({}, $(evt.item).data('section'));
                    $(evt.to).children('.ui-section-holder').each(function (idx) {
                        // re-index the new group
                        prepareSectionInfo(this, idx);
                    });
                    var newSectionData = $(evt.to).closest('.ui-section-holder').data('section');
                    var sectionData = $(evt.item).data('section');
                    if (newSectionData) {
                        newSectionData['relation'] = RELATION_SECTION_CHILDREN;
                        newSectionData['insert_sibling_section_url'] = newSectionData['insert_section_url'];
                    } else {
                        newSectionData = sectionData;
                        newSectionData['relation'] = RELATION_PAGE_CHILDREN;
                    }
                    removeSection(remove_data, () => {
                        var sectionId = $(evt.item).data('section')['id_sections'];
                        var position = ($(evt.item).data('section')['order_position'] * 10) - 5;
                        insertSection(newSectionData, sectionId, true, position);
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
        });
        new Sortable(section, sortableOptions);
    });
    // **************************** NESTED CHILDREN ***********************************
    var childrenSections = $('#ui-cms .section-children-ui-cms');
    Array.from(childrenSections).forEach((section) => {
        $(section).children('.ui-section-holder').each(function (idx) {
            prepareSectionInfo(this, idx);
        });
        new Sortable(section, sortableOptions);
    });
}

/**
 * prepare section info. Calculate parents data, adjust relations and urls
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} section
 * @param {any} idx
 * @returns {any}
 */
function prepareSectionInfo(section, idx) {
    if (DEBUG) {
        console.log(section, idx);
    }
    var sectionData = $(section).data('section');
    sectionData['order_position'] = idx;
    $(section).children('.badge').text(idx + 1); // for debugging
    $(section).attr('data-section', sectionData);
    return sectionData;
}

/**
 * remove section from page or another section depending on the parameters
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} sectionData
 * @param {Function} callback
 * @returns {any}
 */
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

/**
 * reorder section
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {any} sectionData
 * @param {any} order
 * @returns {any}
 */
function reorderSectionsFromRoot(sectionData, order) {
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

/**
 * add already existing section
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {Number} sectionId - the id of the section which will be added
 * @param {Array} sectionData - the section data with info how the section will be added
 * @param {Boolean} addSibling - if true we add a sibling we use the same parent if false we use the section as parent
 * @param {Number} position - the position where it will be added 
 * @returns {any}
 */
function addSection(sectionId, sectionData, addSibling, position) {
    if (sectionId == sectionData['id_sections']) {
        $.alert({
            title: 'CMS UI',
            content: "It is not possible to insert a section inside the same section!"
        });
    } else {
        insertSection(sectionData, sectionId, addSibling, position);
    }
}

/**
 * add already existing section
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {Number} styleId - the id of the style which will be created as a new section
 * @param {Array} sectionData - the section data with info how the section will be added
 * @param {Boolean} addSibling - if true we add a sibling we use the same parent if false we use the section as parent
 * @param {Number} position - the position where it will be added
 * @param {String} styleName
 * @returns {any}
 */
function addNewSection(styleId, sectionData, addSibling, position, styleName) {
    createSection(sectionData, styleId, addSibling, position, styleName);
}

/**
 * show modal for add section
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {Array} sectionData - the section data with info how the section will be added
 * @param {Boolean} addSibling - if true we add a sibling we use the same parent if false we use the section as parent
 * @param {Number} position - the position where it will be added 
 * @returns {any}
 */
function showAddSection(sectionData, addSibling, position) {
    if (DEBUG) {
        console.log("sectionData", sectionData);
        console.log("addSibling", addSibling);
        console.log("position", position);
    }
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
        $.ajax({
            type: "POST",
            url: actionUrl,
            data: data, // serializes the form's elements.
            success: function (data) {
                // update_new_data(data, ['#ui-middle', '#section-ui-card-content>card-body', '#section-ui-card-properties>card-body', '#nav-menu']);
                refresh_cms_ui(['#ui-middle'], () => {
                    $($(data).find('[id^="section-controller-fail"]').get().reverse()).each(function () {
                        $('#ui-middle .sticky-top').prepend(this);
                    })
                    $($(data).find('[id^="section-controller-success"]').get().reverse()).each(function () {
                        $('#ui-middle .sticky-top').prepend(this);
                    })
                });
            }
        });

    });
}

/**
 * insert existing section
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {Number} styleId - the id of the style which will be created as a new section
 * @param {Array} sectionData - the section data with info how the section will be added
 * @param {Boolean} addSibling - if true we add a sibling we use the same parent if false we use the section as parent
 * @param {Number} position - the position where it will be added
 * @returns {any}
 */
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
 * create new section
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {Number} styleId - the id of the style which will be created as a new section
 * @param {Array} sectionData - the section data with info how the section will be added
 * @param {Boolean} addSibling - if true we add a sibling we use the same parent if false we use the section as parent
 * @param {Number} position - the position where it will be added
 * @param {any} styleName
 * @returns {any}
 */
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
        if (sectionData["relation"] == RELATION_PAGE_CHILDREN) {
            url = sectionData['insert_section_in_page'];
        } else {
            url = sectionData['insert_sibling_section_url'];
        }
    }
    return url;
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
                update_new_data(data, ["#ui-middle", '#section-ui-fields-holder', "#section-ui-page-list", '#section-ui-global-page-list', "#section-ui-navigation-hierarchy-list"]);
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
        confirmation('<p>This will delete the page <code>' + delData['name'] + '</code> and all the data associated to this page.<p>Children elements are not affected.</p></p><p>You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, enter the keyword of the page.</p> <input id="deleteValue" type="text" class="form-control" >', () => {
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
                update_new_data(data, ['#ui-middle', '#section-ui-card-content>card-body', '#section-ui-card-properties>card-body', '#nav-menu',
                    "#section-ui-navigation-hierarchy-list", "#header-position", ".style-section-page-order-wrapper", "#section-ui-page-list", '#section-ui-global-page-list', "#cms-alerts"]);
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
    $('.ui-card-properties  :input').on('change', function () { //triggers change in all input fields including text type
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
 * Load section fields
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @param {String} sectionUrl
 * @param {Function} callback
 * @returns {any}
 */
function loadSectionFields(sectionUrl, callback) {
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
            update_new_data(data, ['#section-ui-fields-holder'], callback);
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

