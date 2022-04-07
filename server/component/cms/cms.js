$(document).ready(function () {
    $('[id|=sections]').hover(
        function () {
            var ids = $(this).attr('id').split('-');
            var id = ids[ids.length - 1];
            $('.section-section-' + id).addClass("highlight-hover");
        }, function () {
            var ids = $(this).attr('id').split('-');
            var id = ids[ids.length - 1];
            $('.section-section-' + id).removeClass("highlight-hover");
        }
    );

    init_ui_cms();

    $('.children-list.sortable').each(function (idx) {
        var $input = $(this).prev();
        var $list = $(this);
        $list.sortable("destroy");
        $list.sortable({
            animation: 150,
            onSort: function (evt) {
                var order = [];
                $list.children('li').each(function (idx) {
                    order[$(this).children('.badge').text()] = idx * 10;
                });
                $input.val(order);
            }
        });
    });
    $(function () {
        $('[data-toggle="popover"]').popover({ html: true });
    });
    var $root = $('<div/>');
    $('#section-page-view>.card-body').children('[class*="section-section"]').each(function () {
        traverse_page_view($root, $(this));
        $('.cms-page-overview').append($root);
    })
});

function traverse_page_view($root, $parent) {
    var $new_root = $root;
    var $children = $parent.children();
    var add_leaf = false;
    var has_child = false;
    var has_section_child = false;
    var is_section_child = false;
    if ($parent.is('[class*=section-section]')) {
        var css = "";
        $parent.attr('class').split(' ').map(function (className) {
            if (className.startsWith('section-section')
                || className === "row"
                || className === "col"
                || className === "highlight"
            )
                css += " " + className;
        });
        $new_root = $('<div/>', { "class": "p-0 page-view-element border rounded m-1 " + css });
        $root.append($new_root);
        has_child = true;
        if ($children.length === 0)
            add_leaf = true;
    }
    else
        is_section_child = true;
    $children.each(function () {
        has_section_child |= traverse_page_view($new_root, $(this));
    });
    if (add_leaf | (!has_section_child && !is_section_child))
        $new_root.append('<div class="page-view-element-leaf"></div>');
    return has_child | has_section_child;
}



//******************************************* JS UI for CMS ***********************************************

// Build custom javascript UI.
function init_ui_cms() {
    initChildrenArea();
    initUISectionsButtons();
    initSortableElements();
}

// create a button add nee section above selected section
function addButtonNewSectionAbove(section) {
    var icon = $('<i class="fa-lg fas fa-plus-circle ui-section-btn ui-icon-button-white text-success" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Add new section above"></i>');
    $(icon).click((e) => {
        showAddSection(e, 'above');
    })
    return icon;
}

// create a button add nee section bellow the selected section
function addButtonNewSectionBelow(section) {
    var icon = $('<i class="fa-lg fas fa-plus-circle ui-section-btn ui-icon-button-white text-success" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Add new section below"></i>');
    $(icon).click((e) => {
        e.preventDefault();
        // moveSectionDown(section);
    })
    return icon;
}

// create a new button add new child to selected section. Only sections witch can have children will have this button
function addButtonNewChildToSection(section) {
    var icon = $('<i class="fa-lg fas fa-sign-in-alt ui-section-btn text-success" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Add new child section"></i>');
    $(icon).click(() => {
        console.log('click');
    })
    return icon;
}

// create a button remove the selected section
function addButtonRemoveSection(sectionData) {
    var icon = $('<i class="fa-lg fas fa-minus-circle ui-section-btn text-danger ui-icon-button-white" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Remove the section"></i>');
    $(icon).click(() => {
        removeSection(sectionData);
    })
    return icon;
}

// create a new button for moving the section up
function addButtonMoveSectionUp(section) {
    var icon = $('<i class="fa-lg fas fa-arrow-alt-circle-up ui-section-btn ui-icon-button-white text-primary" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Move the section up"></i>');
    $(icon).click(() => {
        moveSectionUp(section);
    })
    return icon;
}

// create a new button for moving the section down
function addButtonMoveSectionDown(section) {
    var icon = $('<i class="fa-lg fas fa-arrow-alt-circle-down ui-section-btn ui-icon-button-white text-primary" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Move the section down"></i>');
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
    if (sectionData['can_have_children']) {
        $(buttonsHolderAdd).append(addButtonNewChildToSection());
    }
    $(buttonsHolderAdd).append(addButtonNewSectionBelow(section));
    $(buttonsHolder).append(buttonsHolderAdd);
    $(buttonsHolder).append(addButtonRemoveSection(sectionData));
    $(section).append(buttonsHolder);
    $(buttonsHolderUpDown).append(buttonsHolderUpDownButtons);
    $(buttonsHolderUpDownButtons).append(addButtonMoveSectionUp(section));
    $(buttonsHolderUpDownButtons).append(addButtonMoveSectionDown(section));
    $(section).append(buttonsHolderUpDown);
}

// confirmation function
// takes confirmation message and confirmCallback which is executed on confirmation
function confirmation(content, confirmCallback) {
    $.confirm({
        title: 'CMS UI',
        content: content,
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
                callbackSuccess();
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

// refresh the CMS_UI
function refresh_cms_ui() {
    $('.popover').remove(); // first remove all tooltips if they are active
    $.get(location.href, function (data) {
        $('#cms-ui').empty().append($(data).find('#cms-ui').children());
        init_ui_cms(); // reload the UI initialization
        $('[data-toggle="popover"]').popover({ html: true }); // reload again the tooltips
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
        onEnd: function (evt) {
            if (evt.from == evt.to) {
                // re-arrange
                reorderSectionsFromRoot($(evt.item).data('section'), getChildrenOrder(evt.from));
            } else {
                // move from one parent to another
                console.log('Old parent', $(evt.item).data('section')['parent_id']);
                $(evt.to).children('.ui-section-holder').each(function (idx) {
                    // re-index the new group
                    prepareSectionInfo(this, idx);
                });
                console.log('New parent', $(evt.item).data('section')['parent_id'], $(evt.item).data('section')['parent']);
                console.log("New section ", $(evt.item).data('section')['id_sections'], " should have position: ", $(evt.item).data('section')['order_position'] * 10);
                console.log(getChildrenOrder(evt.to));
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
    var sectionSections = $('#section-section-view >.card-body > .ui-section-holder > section-can-have-children');
    Array.from(sectionSections).forEach((section) => {
        $(section).children('.ui-section-holder').each(function (idx) {
            console.log(idx);
            prepareSectionInfo(this, idx);
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
        sectionData['update_url'] = sectionData['section_from_section_url'].replace(':parent_id', parentData['id_sections'])
        sectionData['relation'] = 'section_children';
        sectionData['parent_id'] = parentData['id_sections'];
    } else {
        sectionData['update_url'] = sectionData['section_from_page_url']
        sectionData['relation'] = 'page_children';
        sectionData['parent'] = "page";
        sectionData['parent_id'] = sectionData['id_pages'];
    }

    $(section).children('.badge').text(idx); // for debugging

    $(section).attr('data-section', JSON.stringify(sectionData));
}

// remove section from page or another section depending on the parameters
function removeSection(sectionData) {
    confirmation('Do you really want to remove <code>' + sectionData['section_name'] + '</code>?', () => {
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
                refresh_cms_ui();
            },
            () => {
                console.log('error');
                $.alert({
                    title: 'Error!',
                    content: 'The section was not deleted!',
                });
            });
    });
}

// reorder section
function reorderSectionsFromRoot(sectionData, order) {
    console.log(order);
    // executeAjaxCall(
    //     'post',
    //     sectionData['update_url'],
    //     {
    //         "mode": "update",
    //         "fields": {
    //             sections: {
    //                 1: {
    //                     1: {
    //                         id: "",
    //                         type: "section-list",
    //                         relation: sectionData['relation'],
    //                         content: order
    //                     }
    //                 }
    //             }
    //         }
    //     },
    //     () => {
    //         console.log('re-ordered');
    //         refresh_cms_ui();
    //     },
    //     () => {
    //         console.log('error');
    //         $.alert({
    //             title: 'Error!',
    //             content: 'The section was not re-ordered!',
    //         });
    //     });
}

// add already existing section
// sectionId - int - the id of the section which will be added
// parentData - array - the parent data where the section will be added
// position - int - the position where it will be added 
function addSection(sectionId, parentData, position) {
    console.log('Add section', sectionId, parentData, position);
}

// add already existing section
// sectionId - int - the id of the section which will be the new section
// parentData - array - the parent data where the section will be added
// position - int - the position where it will be added 
function addNewSection(sectionId, parentData, position) {
    console.log('Add new section', sectionId, parentData, position);
}

// show modal for add section
function showAddSection(e, parentData, position) {
    $('#ui-add-section-modal').modal();
    $('#ui-add-section').css({ top: e.clientY - $('#ui-add-section').outerHeight() / 2, left: e.clientX + 10 });
    $('#nav-new-section-tab').tab("show"); //always show the first tab when modal is opened for consistency 
}