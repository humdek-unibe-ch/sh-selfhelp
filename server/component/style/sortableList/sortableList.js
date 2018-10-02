$('.children-list.sortable').each(function(idx) {
    var $list = $(this);
    $list.sortable({
        animation: 150,
    });
});
