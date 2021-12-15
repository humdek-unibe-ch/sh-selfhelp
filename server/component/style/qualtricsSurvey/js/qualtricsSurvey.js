$(document).ready(function () {
    $("iframe").on('load', function () {
        iFrameResize({
            log: false,
            heightCalculationMethod: 'taggedElement'
        });
    });
});