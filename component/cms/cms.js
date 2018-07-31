$(document).ready(function() {
    $('a[id|=list-item-section]').hover(
        function() {
            var ids = $(this).attr('id').split('-');
            $('div#style-section-' + ids[3]).addClass("highlight");
        }, function() {
            var ids = $(this).attr('id').split('-');
            $('div#style-section-' + ids[3]).removeClass("highlight");
        }
    );
});
