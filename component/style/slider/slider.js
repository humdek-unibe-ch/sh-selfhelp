$(document).ready(function() {
    position_slider_legend();
    $(window).resize(position_slider_legend);
});

function position_slider_legend()
{
    $('.slider-legend').each(function() {
        var slider_width = $(this).prev().outerWidth();
        var count = $(this).children().length;
        var step_width = slider_width / (count - 1);
        $(this).children().each(function (index) {
            var pos = 0;
            if(index == 0) 
            {
                $(this).parent().outerHeight($(this).outerHeight());
                pos = 0;
            }
            else if(index == count - 1) pos = slider_width - $(this).outerWidth();
            else pos = step_width * index - $(this).outerWidth()/2;
            $(this).css({marginLeft: pos});
        });
    });
}
