$(document).ready(function () {
    initSortableList();
});

function initSortableList() {
    $('.children-list.sortable').each(function (idx) {
        $(this).sortable();
    });
}
