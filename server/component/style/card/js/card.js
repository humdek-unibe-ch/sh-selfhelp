$(document).ready(function() {
    toggle_collapsible_card($('.style-'
        + window.location.hash.substring(1)
        + ".card:not(.no-anchor-expand) > .card-header.collapsible.collapsed"));
    $('div.card-header.collapsible').on('click', function() {
        toggle_collapsible_card($(this));
    });
});

function toggle_collapsible_card(elem) {
    var $header = elem;
    var $icon = elem.find('i.card-icon-collapse');
    var $collapsible = elem.next();
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
}
