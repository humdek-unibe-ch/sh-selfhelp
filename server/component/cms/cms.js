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

    ui_cms();

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
function ui_cms() {

    var allStyles = $('.ui-style-holder');
    Array.from(allStyles).forEach((style) => {
        addUIStyleButtons(style);
    });

    $(allStyles).hover(
        function () {
            $(this).addClass("ui-style-hover");
        }, function () {
            $(this).removeClass("ui-style-hover");
        }
    );
}

// create a button add nee style above selected style
function addButtonNewStyleAbove(style) {
    var icon = $('<i class="fas fa-plus-circle ui-style-btn bg-white text-success" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Add new style above"></i>');
    $(icon).click(() => {
        console.log('click');
    })
    return icon;
}

// create a button add nee style bellow the selected style
function addButtonNewStyleBelow(style) {
    var icon = $('<i class="fas fa-plus-circle ui-style-btn bg-white text-success" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Add new style below"></i>');
    $(icon).click(() => {
        console.log('click');
    })
    return icon;
}

// create a new button add new child to selected style. Only styles witch can have children will have this button
function addButtonNewChildToStyle(style) {
    var icon = $('<i class="fas fa-sign-in-alt ui-style-btn bg-white text-success" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Add new child style"></i>');
    $(icon).click(() => {
        console.log('click');
    })
    return icon;
}

// create a button remove the selected style
function addButtonRemoveStyle(style) {
    var icon = $('<i class="fas fa-minus-circle ui-style-btn text-danger bg-white" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Remove the style"></i>');
    $(icon).click(() => {
        console.log('click');
    })
    return icon;
}

// add all UI buttons to the styles
function addUIStyleButtons(style) {
    var dataStyle = $(style).data('style');
    var buttonsHolder = $('<div class="ui-buttons-holder position-absolute justify-content-between"></div>');
    var buttonsHolderAdd = $('<div class="d-flex flex-column justify-content-between"></div>');
    $(buttonsHolderAdd).append(addButtonNewStyleAbove());
    if (dataStyle['can_have_children']) {
        $(buttonsHolderAdd).append(addButtonNewChildToStyle());
    }
    $(buttonsHolderAdd).append(addButtonNewStyleBelow());
    $(buttonsHolder).append(buttonsHolderAdd);
    $(buttonsHolder).append(addButtonRemoveStyle());
    $(style).append(buttonsHolder);
}