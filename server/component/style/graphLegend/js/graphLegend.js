$(document).ready(function() {
    $('.graphLegend-item').each(function() {
        let $square = $(this).children('i.graphLegend-square').first();
        let color = $(this).children('div.graphLegend-color').first().text();
        $square.css("color", color);
    });
});
