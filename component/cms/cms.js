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
    var $input = $('.children-list.sortable').prev();
    $('.children-list.sortable').sortable({
        animation: 150,
        onSort : function(evt) {
            var order = [];
            $('.children-list.sortable').children('li').each(function(idx) {
                order[$(this).children('.badge').text()] =  idx * 10;
            });
            $input.val(order);
        }
    });
    $('button[data-target="#remove-section-association"]').click(function() {
        var ids = $(this).parent().attr('id').split('-');
        $('input[name="remove-section-link"]').val(ids[ids.length-1]);
    });
    $('span[id|="global-sections"').click(function() {
        var ids = $(this).attr('id').split('-');
        $('span[id|="global-sections"').removeClass("active");
        $(this).addClass("active");
        $('input[name="section-name"]').val($(this).text());
        $('input[name="add-section-link"]').val(ids[ids.length - 2]);
        $('input[name="new-section"]')
            .prop("checked", false)
            .attr("disabled", true);
        console.log(ids);
        $('select[name="section-style"]').val(ids[ids.length - 1]);
        $(this).parents("div.card-body.collapse:first").hide('fast', function() {
            $(this).removeClass("show");
            $(this).prev().addClass("collapsed");
        });
    });
    $('input[name="section-name"]').keyup(function(){
        $('input[name="new-section"]')
            .prop("checked", true)
            .attr("disabled", true);
        $('input[name="add-section-link"]').val("");
    });
});
