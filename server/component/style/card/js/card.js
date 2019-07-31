$(document).ready(function() {
    $('div.card-header.collapsible').on('click', function() {
        var $header = $(this);
        var $collapsible = $(this).next();
        if($collapsible.hasClass("show")) {
            $collapsible.hide('fast', function() {
                $collapsible.removeClass("show");
                $header.addClass("collapsed");
            });
        }
        else
        {
            $collapsible.show('fast', function () {
                $collapsible.addClass("show");
                $header.removeClass("collapsed");
            });
        }
    });
});
