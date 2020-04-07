$(document).ready(() => {
    $('div.filter-toggle').each(function() {
        let $filter_data = $(this).children('div.filter-toggle-data');
        let $filter_switch = $(this).children('button.filter-toggle-switch');
        let $spinner = $filter_switch.children('i.filter-toggle-pending');
        let filter_data = parseFilterData($filter_data);
        let event_name = "data-filter-" + filter_data.data_source;;
        let event = new Event(event_name);
        $filter_switch.click(function() {
            $spinner.removeClass('d-none');
            $.post(
                BASE_PATH + '/request/AjaxDataSource/set_data_filter',
                {
                    action: $(this).hasClass('active') ? "rm" : "add",
                    name: filter_data.name,
                    value: filter_data.value,
                    data_source: filter_data.data_source
                },
                function(data) {
                    if(data.success)
                    {
                        console.log("dispatch event: " + event_name);
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
