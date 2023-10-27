$(document).ready(function () {
    initNestedList();
});

function initNestedList() {
    $('.list-search').on('keyup', function () {
        var pattern = $(this).val();
        var $list = $(this).parent().next().find('a,span');
        $list.each(function (index) {
            var label = $(this).text();
            if (label.search(pattern) < 0) $(this).parent().hide();
            else {
                $(this).parent().show();
                $(this).parents('div.collapse').collapse('show');
            }
        });
    });
    $('.clear-search').on('click', function () {
        $(this).prev().val("");
        var $list = $(this).parent().next().find('a,span');
        $list.each(function (index) {
            $(this).parent().show();
        });
    });
    $('.nested-list a').on('click', function (e) {
        e.stopPropagation();
    });
    $('.nested-list div.collapsible').on('click', function (e) {
        var $collapsible = $(this).next();
        if ($collapsible.hasClass("show")) {
            $collapsible.hide('fast', function () {
                $collapsible.removeClass("show");
            });
        }
        else {
            $collapsible.show('fast', function () {
                $collapsible.addClass("show");
            });
        }
        var $chevron = $(this).children('.fas:first');
        if ($chevron.hasClass('fa-chevron-right'))
            $chevron
                .removeClass('fa-chevron-right')
                .addClass('fa-chevron-down');
        else if ($chevron.hasClass('fa-chevron-down'))
            $chevron
                .removeClass('fa-chevron-down')
                .addClass('fa-chevron-right');
    });
    $('.nested-list-menu-responsive.collapsed').click(function () {
        var $collapsible = $(this).next();
        if ($collapsible.hasClass("show")) {
            $collapsible.hide(function () {
                $collapsible.removeClass("show");
            });
        }
        else {
            $collapsible.show(function () {
                $collapsible.addClass("show");
            });
        }
    });
}
