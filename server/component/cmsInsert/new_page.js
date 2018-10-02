$(document).ready(function() {
    var $url_input = $("input[name='url']");
    var $pos_list = $("div#page-order-wrapper > ul.children-list");
    var $check_pos_list = $('input[name="set-position"]');
    var $protocol_post = $('input[value="POST"]');
    var $protocol_put = $('input[value="PUT"]');
    var $protocol_patch = $('input[value="PATCH"]');
    var $protocol_delete = $('input[value="DELETE"]');
    var $type_component = $('input[name="type"][value="2"]');
    var $type_custom = $('input[name="type"][value="1"]');
    var keyword = "";
    var nav = "";
    $('input[name="set-user_input"]').change(function() {
        if($(this).is(":checked"))
            $protocol_post.prop("checked", true);
    });
    $('input[name="set-advanced"]').change(function() {
        if($(this).is(":checked"))
        {
            $url_input.prop("readonly", false);
            $protocol_put.prop("disabled", false);
            $protocol_patch.prop("disabled", false);
            $protocol_delete.prop("disabled", false);
            $type_component.prop("disabled", false);
            $type_custom.prop("disabled", false);
        }
        else
        {
            $url_input.prop("readonly", true);
            $protocol_put.prop("disabled", true);
            $protocol_patch.prop("disabled", true);
            $protocol_delete.prop("disabled", true);
            $type_component.prop("disabled", true);
            $type_custom.prop("disabled", true);
        }
    });
    $('input[name="keyword"]').keyup(function() {
        keyword = $(this).val();
        $url_input.val("/" + keyword + nav);
        $('#sections-field-new > .label').text($(this).val());
    });
    $('input[name="type"]').change(function() {
        if($(this).val() == 4)
            nav = "/[i:nav]";
        else
            nav = "";
        $url_input.val("/" + keyword + nav);
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
