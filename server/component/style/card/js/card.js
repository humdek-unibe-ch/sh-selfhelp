$(document).ready(function() {
    toggle_collapsible_card($('.style-'
        + window.location.hash.substring(1)
        + ".card:not(.no-anchor-expand) > .card-header.collapsible.collapsed"));
    initCard();
});

function initCard(){
    $('div.card-header.collapsible').off('click').on('click', function () {
        setTimeout(() => {
            $('.CodeMirror-sizer').each(function () {
                $(this).css('min-height', '32px'); // ugly hack for hidden fields to properly get height
            })
        }, 100);
        toggle_collapsible_card($(this)); // this function is in style cards card.js 
    });
}

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
