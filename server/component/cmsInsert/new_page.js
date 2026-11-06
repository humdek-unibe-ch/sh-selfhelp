$(document).ready(function() {
    var $url_input = $("input[name='url']");
    var $pos_list = $("div#page-order-wrapper > ul.children-list");
    var $check_pos_list = $('input[name="set-position"]');
    var $protocol_list = $('#protocol-list');
    var $headless_check = $('#headless-check');
    var $header_pos = $('#header-position');
    var $type_component = $('input[name="type"][value="2"]');
    var $type_custom = $('input[name="type"][value="1"]');
    var $type_ajax = $('input[name="type"][value="5"]');
    var keyword = "";
    var nav = "";
    $('input[name="set-advanced"]').change(function() {
        if($(this).is(":checked"))
        {
            $url_input.prop("readonly", false);
            $type_component.prop("disabled", false);
            $type_custom.prop("disabled", false);
            $type_ajax.prop("disabled", false);
            $protocol_list.removeClass("d-none");
            $headless_check.removeClass("d-none");
        }
        else
        {
            $url_input.prop("readonly", true);
            $type_component.prop("disabled", true);
            $type_custom.prop("disabled", true);
            $type_ajax.prop("disabled", true);
            $protocol_list.addClass("d-none");
            $headless_check.addClass("d-none");
        }
    });
    $('input[name="keyword"]').on('keyup input', function() {
        keyword = $(this).val();
        $url_input.val("/" + keyword + nav);
        $pos_list.find('.ui-menu-position-self .label').text(keyword || 'New Page');
    });
    $('input[name="type"]').change(function() {
        if($(this).val() == 4)
        {
            nav = "/[i:nav]";
            $check_pos_list.prop("checked", false);
            $check_pos_list.trigger("change");
        }
        else
        {
            nav = "";
        }
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
        var setOrder = function() {
            var order = [];
            $list.children('li').each(function(idx) {
                order[$(this).attr('data-index')] = idx * 10; // the key is the position when the list was rendered
            });
            $check_pos_list.val(order.toString());
        };
        $list.sortable("destroy");
        $list.sortable({
            animation: 150,
            onSort : setOrder,
            filter: ".fixed",
        });
        // the arrows move the new page one position up or down, like a drag
        $list.on('click', '.ui-menu-position-step', function() {
            var $self = $list.children('.ui-menu-position-self');
            var step = parseInt($(this).attr('data-step'), 10);
            if (step < 0 && $self.prev().length) {
                $self.insertBefore($self.prev());
            } else if (step > 0 && $self.next().length) {
                $self.insertAfter($self.next());
            } else {
                return;
            }
            $self.find('.ui-menu-position-step[data-step="' + step + '"]').trigger('focus');
            setOrder();
        });
    });
});
