$(document).ready(function() {
    $('div.card-header.collapsible').on('click', function() {
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
