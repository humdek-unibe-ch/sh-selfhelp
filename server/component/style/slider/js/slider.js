$(document).ready(function () {
    new ResizeSensor(jQuery('.selfhelp-slider'), function () {
        position_slider_legend();
    });
    position_slider_legend();
    check_slider_locked_after_submit();
});

function position_slider_legend() {
    $('.slider-legend').each(function () {
        var slider_width = $(this).prev().outerWidth();
        var count = $(this).children().length;
        var step_width = slider_width / (count - 1);
        $(this).children().each(function (index) {
            var pos = 0;
            if (index == 0) {
                $(this).parent().outerHeight($(this).outerHeight());
                pos = 0;
            }
            else if (index == count - 1) pos = slider_width - $(this).outerWidth();
            else pos = step_width * index - $(this).outerWidth() / 2;
            $(this).css({ marginLeft: pos });
        });
    });
}

function check_slider_locked_after_submit() {
    $('.selfhelpSlider').each(function () {
        if ($(this).data('locked_after_submit') && ($(this).data('value') != '' || $(this).data('value') === 0)) {
            $(this).css('pointer-events', 'none');
            var slider = this;
            var val = $(this).val();
            $(this).on('change', function () {
                $(slider).val(val);
            });
        }
    })
}
