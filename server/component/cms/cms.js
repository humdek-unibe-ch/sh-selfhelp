$(document).ready(function() {
    $('[id|=sections]').hover(
        function() {
            var ids = $(this).attr('id').split('-');
            var id = ids[ids.length-1];
            $('.style-section-' + id).addClass("highlight-hover");
        }, function() {
            var ids = $(this).attr('id').split('-');
            var id = ids[ids.length-1];
            $('.style-section-' + id).removeClass("highlight-hover");
        }
    );
    $('.children-list.sortable').each(function(idx) {
        var $input = $(this).prev();
        var $list = $(this);
        $list.sortable("destroy");
        $list.sortable({
            animation: 150,
            onSort : function(evt) {
                var order = [];
                $list.children('li').each(function(idx) {
                    order[$(this).children('.badge').text()] =  idx * 10;
                });
                $input.val(order);
            }
        });
    });
    $(function () {
        $('[data-bs-toggle="popover"]').popover({html:true});
    });
    var $root = $('<div/>');
    $('#section-page-view>.card-body').children('[class*="style-section"]').each(function() {
        traverse_page_view($root, $(this));
        $('.cms-page-overview').append($root);
    })
    initPageOrder();
});

function traverse_page_view($root, $parent)
{
    var $new_root = $root;
    var $children = $parent.children();
    var add_leaf = false;
    var has_child = false;
    var has_style_child = false;
    var is_style_child = false;
    if($parent.is('[class*=style-section]'))
    {
        var css = "";
        $parent.attr('class').split(' ').map(function(className) {
            if(className.startsWith('style-section')
                || className === "row"
                || className === "col"
                || className === "highlight"
            )
                css += " " + className;
        });
        $new_root = $('<div/>', {"class": "p-0 page-view-element border rounded m-1 " + css});
        $root.append($new_root);
        has_child = true;
        if($children.length === 0)
            add_leaf = true;
    }
    else
        is_style_child = true;
    $children.each(function() {
        has_style_child |= traverse_page_view($new_root, $(this));
    });
    if(add_leaf | (!has_style_child && !is_style_child))
        $new_root.append('<div class="page-view-element-leaf"></div>');
    return has_child | has_style_child;
}


/**
 * Init page order
 * @author Stefan Kodzhabashev
 * @date 2022-07-20
 * @returns {any}
 */
function initPageOrder() {

    function showHideList(checkbox) {
        if ($(checkbox).is(":checked")) {
            $(checkbox).next().removeClass("text-muted");
            pos_list.parent().removeClass("d-none");
        }
        else {
            $(checkbox).next().addClass("text-muted");
            pos_list.parent().addClass("d-none");
        }
    }

    function setOrder(list) {
        var order = [];
        list.children('li').each(function (idx) {
            order[$(this).children('.badge').text()] = idx * 10;
        });
        check_pos_list.val(order.toString());
    }

    var pos_list = $("div.style-section-page-order-wrapper > ul.children-list");
    var check_pos_list = $('input[name="set-position"]');
    check_pos_list.each(function () {
        showHideList(this);
    })

    check_pos_list.off('change').on('change', function () {
        showHideList(this);
        var nav_pos = $('input[name="nav_position"]');
        if (!$(nav_pos).data('nav-position')) {
            nav_pos.attr('data-nav-position', nav_pos.val());
        }
        if (typeof unsavedChanges !== 'undefined') {
            unsavedChanges.push(this);
        }
    });

    pos_list.each(function (idx) {
        var list = $(this);
        setOrder(list);
        list.sortable("destroy");
        list.sortable({
            animation: 150,
            onSort: function (evt) {
                setOrder(list);
                if (typeof unsavedChanges !== 'undefined') {
                    unsavedChanges.push(this);
                }
            },
            filter: ".fixed",
        });
    });
}