$(document).ready(function () {
    $('[id|=sections]').hover(
        function () {
            var ids = $(this).attr('id').split('-');
            var id = ids[ids.length - 1];
            $('.style-section-' + id).addClass("highlight-hover");
        }, function () {
            var ids = $(this).attr('id').split('-');
            var id = ids[ids.length - 1];
            $('.style-section-' + id).removeClass("highlight-hover");
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
    $('#section-page-view>.card-body').children('[class*="style-section"]').each(function () {
        traverse_page_view($root, $(this));
        $('.cms-page-overview').append($root);
    })
});

function traverse_page_view($root, $parent) {
    var $new_root = $root;
    var $children = $parent.children();
    var add_leaf = false;
    var has_child = false;
    var has_style_child = false;
    var is_style_child = false;
    if ($parent.is('[class*=style-section]')) {
        var css = "";
        $parent.attr('class').split(' ').map(function (className) {
            if (className.startsWith('style-section')
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
        is_style_child = true;
    $children.each(function () {
        has_style_child |= traverse_page_view($new_root, $(this));
    });
    if (add_leaf | (!has_style_child && !is_style_child))
        $new_root.append('<div class="page-view-element-leaf"></div>');
    return has_child | has_style_child;
}



//******************************************* JS UI for CMS ***********************************************

// Build custom javascript UI.
function init_ui_cms() {
    initChildrenArea();
    initUIStylesButtons();
    initSortableElements();
}

// create a button add nee style above selected style
function addButtonNewStyleAbove(style) {
    var icon = $('<i class="fas fa-plus-circle ui-style-btn ui-icon-button-white text-success" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Add new style above"></i>');
    $(icon).click(() => {
        moveStyleUp(style);
    })
    return icon;
}

// create a button add nee style bellow the selected style
function addButtonNewStyleBelow(style) {
    var icon = $('<i class="fas fa-plus-circle ui-style-btn ui-icon-button-white text-success" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Add new style below"></i>');
    $(icon).click((e) => {
        e.preventDefault();
        moveDown(style);
    })
    return icon;
}

// create a new button add new child to selected style. Only styles witch can have children will have this button
function addButtonNewChildToStyle(style) {
    var icon = $('<i class="fas fa-sign-in-alt ui-style-btn text-success" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Add new child style"></i>');
    $(icon).click(() => {
        console.log('click');
    })
    return icon;
}

// create a button remove the selected style
function addButtonRemoveStyle(styleData) {
    var icon = $('<i class="fas fa-minus-circle ui-style-btn text-danger ui-icon-button-white" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Remove the style"></i>');
    $(icon).click(() => {
        removeStyle(styleData);
    })
    return icon;
}

// create a new button for moving the style up
function addButtonMoveStyleUp(style) {
    var icon = $('<i class="fas fa-arrow-alt-circle-up ui-style-btn ui-icon-button-white text-primary" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Move the style up"></i>');
    $(icon).click(() => {
        moveStyleUp(style);
    })
    return icon;
}

// create a new button for moving the style down
function addButtonMoveStyleDown(style) {
    var icon = $('<i class="fas fa-arrow-alt-circle-down ui-style-btn ui-icon-button-white text-primary" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Move the style down"></i>');
    $(icon).click(() => {
        moveDown(style);
    })
    return icon;
}

// add all UI buttons to the styles
function addUIStyleButtons(style) {
    var styleData = $(style).data('style');
    var buttonsHolder = $('<div class="ui-buttons-holder position-absolute justify-content-between"></div>');
    var buttonsHolderUpDown = $('<div class="ui-buttons-holder position-absolute justify-content-between"></div>');
    var buttonsHolderUpDownButtons = $('<div class="d-flex flex-column justify-content-between m-auto h-100"></div>');
    var buttonsHolderAdd = $('<div class="d-flex flex-column justify-content-between"></div>');
    $(buttonsHolderAdd).append(addButtonNewStyleAbove(style));
    if (styleData['can_have_children']) {
        $(buttonsHolderAdd).append(addButtonNewChildToStyle());
    }
    $(buttonsHolderAdd).append(addButtonNewStyleBelow(style));
    $(buttonsHolder).append(buttonsHolderAdd);
    $(buttonsHolder).append(addButtonRemoveStyle(styleData));
    $(style).append(buttonsHolder);
    $(buttonsHolderUpDown).append(buttonsHolderUpDownButtons);
    $(buttonsHolderUpDownButtons).append(addButtonMoveStyleUp(style));
    $(buttonsHolderUpDownButtons).append(addButtonMoveStyleDown(style));
    $(style).append(buttonsHolderUpDown);
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

// move style up if possible
function moveStyleUp(style) {
    style = $(style);
    var prev = style.prev();
    if (prev.length == 0)
        return;
    prev.css('z-index', 999).css('position', 'relative').animate({ top: style.height() }, 250);
    style.css('z-index', 1000).css('position', 'relative').animate({ top: '-' + prev.height() }, 300, function () {
        prev.css('z-index', '').css('top', '').css('position', '');
        style.css('z-index', '').css('top', '').css('position', '');
        style.insertBefore(prev);
    });
}

// move style down if possible
function moveDown(style) {
    style = $(style);
    var next = style.next();
    if (next.length == 0)
        return;
    next.css('z-index', 999).css('position', 'relative').animate({ top: '-' + style.height() }, 250);
    style.css('z-index', 1000).css('position', 'relative').animate({ top: next.height() }, 300, function () {
        next.css('z-index', '').css('top', '').css('position', '');
        style.css('z-index', '').css('top', '').css('position', '');
        style.insertAfter(next);
        reorderStylesFromRoot('');
    });
}

// add area for the styles with children
function addChildrenArea(styleHolder) {
    var style = $(styleHolder).children(":first")[0];
    var styleHTML = $(style).html();
    $(style).html('')
    var childrenArea = $('<div class="style-children-ui-cms border rounded"></div>');
    $(childrenArea).html(styleHTML);
    $(style).append(childrenArea);
}

// init children area for the styles that can have children
function initChildrenArea() {
    var allStylesWithChildren = $('.style-can-have-children');
    Array.from(allStylesWithChildren).forEach((style) => {
        addChildrenArea(style);
    });
}

// init all style and add buttons to them
function initUIStylesButtons() {
    var allStyles = $('.ui-style-holder');
    Array.from(allStyles).forEach((style) => {
        addUIStyleButtons(style);
    });
}

// init all styles and make them sortable for faster reordering and re-arranging
function initSortableElements() {
    var sortableOptions = {
        scroll: true,
        bubbleScroll: true,
        // multiDrag: true, // Enable the plugin
        // multiDragKey : 'Control',
        // selectedClass: "bg-danger",
        group: 'nested',
        fallbackOnBody: false,
        sort: true,
        animation: 150,
        swapThreshold: 0.65,
        ghostClass: 'drag-ghost',
        onSort: function (evt) {
            var order = [];
            $(evt.from).children('.ui-style-holder').each(function (idx) {
                var style = this;
                var styleData = $(style).data('style');
                order[styleData['order_position']] = idx * 10;
            });
            if (evt.from == evt.to) {
                var styleData = $(evt.item).data('style');
                console.log(styleData);
                console.log(order);
                reorderStylesFromRoot(styleData, order.join());
            }
        }
    }

    // **************************** SECTION PAGE ***********************************
    var pageStyles = $('#section-page-view .card-body');
    Array.from(pageStyles).forEach((styleHolder) => {
        $(styleHolder).children('.ui-style-holder').each(function (idx) {
            prepareStyleInfo(this, idx);
        });
        new Sortable(styleHolder, sortableOptions);
    });
    // **************************** SECTION VIEW ***********************************
    var sectionStyles = $('#section-section-view .card-body');
    Array.from(sectionStyles).forEach((style) => {
        $(style).children('.ui-style-holder').each(function (idx) {
            prepareStyleInfo(this, idx);
        });
        new Sortable(style, sortableOptions);
    });
    // **************************** NESTED CHILDREN ***********************************
    var childrenStyles = $('.style-children-ui-cms');
    Array.from(childrenStyles).forEach((style) => {
        $(style).children('.ui-style-holder').each(function (idx) {
            prepareStyleInfo(this, idx);
        });
        new Sortable(style, sortableOptions);
    });
}

// prepare style info. Calculate parents data, adjust relations and urls
function prepareStyleInfo(style, idx) {
    var styleData = $(style).data('style');
    styleData['order_position'] = idx;
    var parents = $(style).parents('.ui-style-holder');
    var parent
    if (parents) {
        parent = parents[0];
    }
    parentData = $(parent).data('style');
    if (parentData) {
        styleData['update_url'] = styleData['style_from_style_url'].replace(':parent_id', parentData['id_sections'])
        styleData['relation'] = 'section_children';
    } else {
        styleData['update_url'] = styleData['style_from_page_url']
        styleData['relation'] = 'page_children';
    }

    $(style).children('.badge').text(idx); // for debugging

    $(style).attr('data-style', JSON.stringify(styleData));
}

// remove style from page or another style depending on the parameters
function removeStyle(styleData) {
    confirmation('Do you really want to remove <code>' + styleData['section_name'] + '</code>?', () => {
        executeAjaxCall(
            'post',
            styleData['update_url'],
            {
                "remove-section-link": styleData['id_sections'],
                "mode": "delete",
                "relation": styleData['relation']
            },
            () => {
                console.log('deleted');
                refresh_cms_ui();
            },
            () => {
                console.log('error');
                $.alert({
                    title: 'Error!',
                    content: 'The style was not deleted!',
                });
            });
    });
}

// reorder style
function reorderStylesFromRoot(styleData, order) {
    executeAjaxCall(
        'post',
        styleData['update_url'],
        {
            "mode": "update",
            "fields": {
                sections: {
                    1: {
                        1: {
                            id: "",
                            type: "style-list",
                            relation: styleData['relation'],
                            content: order
                        }
                    }
                }
            }
        },
        () => {
            console.log('re-ordered');
            refresh_cms_ui();
        },
        () => {
            console.log('error');
            $.alert({
                title: 'Error!',
                content: 'The style was not re-ordered!',
            });
        });
}