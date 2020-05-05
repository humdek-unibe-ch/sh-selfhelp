function filterInit(type, cb) {
    $('div.filter-' + type).each(function(idx) {
        let $filter = $(this);
        let filter_data = parseFilterData(
            $filter.children('div.filter-data'));
        let event = new CustomEvent("data-filter-" + filter_data.data_source, {});
        cb($filter, filter_data, event);
    });
}

function filterEventHandler($spinner, $filter_switch, idx, filter_data, event) {
    $spinner.removeClass('d-none');
    $.post(
        BASE_PATH + '/request/AjaxDataSource/set_data_filter',
        {
            action: $filter_switch.hasClass('active') ? "rm" : "add",
            name: filter_data.name,
            value: filter_data.value[idx],
            value_idx: idx,
            data_source: filter_data.data_source
        },
        function(data) {
            if(data.success)
            {
                console.log("dispatch event: " + event.type);
                window.dispatchEvent(event);
                $spinner.addClass('d-none');
                $filter_switch.toggleClass('active');
            }
            else {
                console.log(data);
            }
        },
        'json'
    );
}

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
