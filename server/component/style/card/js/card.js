$(document).ready(function() {
    $('div.card-header.collapsible').on('click', function() {
        var $header = $(this);
        var $icon = $(this).find('i.card-icon-collapse');
        var $collapsible = $(this).next();
        if($collapsible.hasClass("show")) {
            $collapsible.hide('fast', function() {
                $collapsible.removeClass("show");
                $header.addClass("collapsed");
                $icon.removeClass("fa-angle-double-up");
                $icon.addClass("fa-angle-double-down");
            });
        }
        else
        {
            $collapsible.show('fast', function () {
                $collapsible.addClass("show");
                $header.removeClass("collapsed");
                $icon.removeClass("fa-angle-double-down");
                $icon.addClass("fa-angle-double-up");
            });
        }
    });
});
