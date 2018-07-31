$(document).ready(function() {
    $('a[id|=page_sections],a[id|=sections]').hover(
        function() {
            var ids = $(this).attr('id').split('-');
            $('div#style-section-' + ids[1]).addClass("highlight");
        }, function() {
            var ids = $(this).attr('id').split('-');
            $('div#style-section-' + ids[1]).removeClass("highlight");
        }
    );
});
