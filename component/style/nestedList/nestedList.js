$(document).ready(function() {
    $('.list-search').on('keyup', function() {
        var pattern = $(this).val();
        var $list = $(this).parents('.card-body:first').find('a.list-group-item');
        $list.each(function(index) {
            var label = $(this).children('span.label').text();
            if(label.search(pattern) < 0) $(this).hide();
            else {
                $(this).show();
                $(this).parents('div.collapse').collapse('show');
            }
        });
    });
    $('.clear-search').on('click', function() {
        $(this).prev().val("");
        var $list = $(this).parents('.card-body:first').find('a.list-group-item');
        $list.each(function(index) {
            $(this).show();
        });
    });
    $('div[id|=collapse-item]').on('shown.bs.collapse', function() {
        $(this).prev().children('.fas:first')
        .removeClass('fa-chevron-right')
        .addClass('fa-chevron-down');
    });
    $('div[id|=collapse-item]').on('hidden.bs.collapse', function() {
        $(this).prev().children('.fas:first')
        .addClass('fa-chevron-right')
        .removeClass('fa-chevron-down');
    });
    $('div.card-header.nested-list').on('click', function() {
        var $collapsible = $(this).next();
        if($collapsible.hasClass("show")) {
            $collapsible.hide('fast', function() {
                $collapsible.removeClass("show");
            });
        }
        else
        {
            $collapsible.show('fast', function () {
                $collapsible.addClass("show");
            });
        }
    });
});
