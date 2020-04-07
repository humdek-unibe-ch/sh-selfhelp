$(document).ready(() => {
    $('div.filter-toggle').each(function() {
        var active = false;
        var filter_data = $(this).children('div.filter-toggle-data').html();
        var $filter_switch = $(this).children('div.filter-toggle-switch');
        $('.bool-filter').click(function() {
            var event_name = "data-filter";
            var event = new CustomEvent(event_name, {detail: {
                action: active ? "rm" : "add",
                name: filter_data.name,
                value: filter_data.value,
                data_source: filter_data.data_source
            }});
            window.dispatchEvent(event);
            $filter_switch.toggleClass('active');
            active = !active;
        });
    });
});
