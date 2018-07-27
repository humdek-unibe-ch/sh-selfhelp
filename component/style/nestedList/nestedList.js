$(document).ready(function() {
    $('.list-group-item').on('click', function() {
        $('.fas', this)
        .toggleClass('fa-chevron-right')
        .toggleClass('fa-chevron-down');
    });
    $('.list-search').on('keyup', function() {
        var pattern = $(this).val();
        var $list = $(this).parents('.card.card-body:first').find('a.list-group-item');
        $list.each(function(index) {
            var label = $(this).children('span.label').text();
            if(label.search(pattern) < 0) $(this).hide();
            else $(this).show();
        });
    });
    $('.clear-search').on('click', function() {
        $(this).prev().val("");
        var $list = $(this).parents('.card.card-body:first').find('a.list-group-item');
        $list.each(function(index) {
            $(this).show();
        });
    });
});
