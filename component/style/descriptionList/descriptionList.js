$(document).ready(function() {
    var input = $('.children-list.sortable').prev();
    $('.children-list.sortable').sortable({
        onSort : function(evt) {
            var order = [];
            $('.children-list.sortable').children().each(function(idx) {
                order[idx] = $(this).children('.badge').text() * 10;
            });
            input.val(order);
            console.log(order);
        }
    });
});
