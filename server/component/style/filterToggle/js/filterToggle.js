$(document).ready(() => {
    $('div.filter-toggle').each(function() {
        var active = false;
        var $filter_data = $(this).children('div.filter-toggle-data');
        var $filter_switch = $(this).children('button.filter-toggle-switch');
        var filter_data = parseFilterData($filter_data);
        $filter_switch.click(function() {
            var event_name = "data-filter-" + filter_data.data_source;;
            var event = new CustomEvent(event_name, {detail: {
                action: active ? "rm" : "add",
                name: filter_data.name,
                value: filter_data.value,
                data_source: filter_data.data_source
            }});
            console.log(event);
            window.dispatchEvent(event);
            $(this).toggleClass('active');
            active = !active;
        });
    });
});

function parseFilterData($data) {
    var j_data = $data.text();
    try {
        return JSON.parse(j_data);
    }
    catch (e) {
        console.log("cannot parse raw data of graph");
        console.log(e);
        return null;
    }
}
