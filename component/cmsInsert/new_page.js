$(document).ready(function() {
    var $url_input = $("input[name='url']");
    var $pos_list = $("div#page-order-wrapper > ul.children-list");
    var $check_pos_list = $('input[name="set-position"]');
    var $url;
    $('input[name="url-manual"]').change(function() {
        if($(this).is(":checked"))
        {
            $(this).next().removeClass("text-muted");
            $url_input.prop("readonly", false);
        }
        else
        {
            $(this).next().addClass("text-muted");
            $url_input.prop("readonly", true);
        }
    });
    $('input[name="keyword"]').keyup(function() {
        $url = "/" + $(this).val();
        $url_input.val($url);
        $('#sections-field-new > .label').text($(this).val());
    });
    $('input[name="type"]').change(function() {
        if($(this).val() == 4)
            $url_input.val($url + "/[i:nav]");
        else
            $url_input.val($url);
    });
    $check_pos_list.change(function() {
        if($(this).is(":checked"))
        {
            $(this).next().removeClass("text-muted");
            $pos_list.parent().removeClass("d-none");
        }
        else
        {
            $(this).next().addClass("text-muted");
            $pos_list.parent().addClass("d-none");
        }
    });
    $pos_list.each(function(idx) {
        var $list = $(this);
        $list.sortable({
            animation: 150,
            onSort : function(evt) {
                var order = [];
                $list.children('li').each(function(idx) {
                    order[$(this).children('.badge').text()] =  idx * 10;
                });
                $check_pos_list.val(order.toString());
            },
            filter: ".fixed",
        });
    });
});
