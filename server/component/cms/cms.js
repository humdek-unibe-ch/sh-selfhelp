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
        $('[data-toggle="popover"]').popover({html:true});
    });
    var $root = $('<div/>');
    var $parent = $('.page-view');
    traverse_page_view($root, $parent.children().first());
    $parent.html($root);
});

function traverse_page_view($root, $parent)
{
    var $new_root = $root;
    if($parent.is('[class*=style-section]')
        && $parent.css('display') !== 'inline'
        && $parent.css('display') !== 'inline-block')
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
        $new_root = $('<div/>', {"class": "page-view-element border rounded m-1 " + css});
        $root.append($new_root);
    }
    $parent.children().each(function() {
        traverse_page_view($new_root, $(this));
    });
}
