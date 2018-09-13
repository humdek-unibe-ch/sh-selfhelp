$(document).ready(function() {
    $('[id|=sections]').hover(
        function() {
            var ids = $(this).attr('id').split('-');
            var id = ids[ids.length-1];
            $('div#style-section-' + id).addClass("highlight-hover");
            $('div#style-section-' + id).removeClass("d-inline");
        }, function() {
            var ids = $(this).attr('id').split('-');
            var id = ids[ids.length-1];
            $('div#style-section-' + id).removeClass("highlight-hover");
            $('div#style-section-' + id).addClass("d-inline");
        }
    );
    $('.children-list.sortable').each(function(idx) {
        var $input = $(this).prev();
        var $list = $(this);
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
});
