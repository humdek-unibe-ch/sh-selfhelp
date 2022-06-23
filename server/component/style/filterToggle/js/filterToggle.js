$(document).ready(() => {
    initFilterToggle();
});

function initFilterToggle(){
    filterInit('toggle', filterToggleActionHandler);
}

function filterToggleActionHandler($filter, filter_data, event) {
    $filter.find('button.filter-toggle-switch').each(function(idx) {
        let $switch = $(this);
        let $spinner = $switch.children('i.filter-toggle-pending');
        $switch.click(function() {
            filterEventHandler($spinner, $switch, idx, filter_data, event);
        });
    });
}
