$(document).ready(function() {
    $('[id|=sections]').hover(
        function() {
            var ids = $(this).attr('id').split('-');
            var id = ids[ids.length-1];
            $('div#style-section-' + id).addClass("highlight-hover");
        }, function() {
            var ids = $(this).attr('id').split('-');
            var id = ids[ids.length-1];
            $('div#style-section-' + id).removeClass("highlight-hover");
        }
    );
});
