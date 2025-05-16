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
    $('input[name="keyword"]').keyup(function() {
        keyword = $(this).val();
        $url_input.val("/" + keyword + nav);
        $('#sections-field-new > .label').text($(this).val());
    });
    // Handle the advanced options checkbox
    $('#advanced-options').change(function() {
        if($(this).is(':checked')) {
            // Enable all action type radio buttons
            $('.action-type-radio').prop('disabled', false);
        } else {
            // Disable advanced action types
            $('.advanced-action .action-type-radio').prop('disabled', true);
            
            // If a disabled option was selected, select the first enabled option
            if($('.action-type-radio:checked').prop('disabled')) {
                $('.action-type-radio:not(:disabled)').first().prop('checked', true).trigger('change');
            }
        }
    });
    
    // Handle action type change
    $('input[name="type"]').change(function() {
        // Check if the selected value is for navigation (lookup_code = 'navigation')
        // We need to check the data attribute or use a known ID since we're now using dynamic IDs
        if($(this).closest('.form-check-inline').find('.form-check-label').text().toLowerCase() === 'navigation')
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
            $(this).next().removeClass("text-body-secondary");
            $pos_list.parent().removeClass("d-none");
        }
        else
        {
            $(this).next().addClass("text-body-secondary");
            $pos_list.parent().addClass("d-none");
        }
    });
    $pos_list.each(function(idx) {
        var $list = $(this);
        $list.sortable("destroy");
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
