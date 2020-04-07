$(document).ready(() => {
    $('div.graph-base').each(function () {
        let $plot = $(this).children('div.graph-plot');
        var raw = parseGraphData($(this).children('div.graph-data:first'));
        if(raw === null) return;

        drawGraph($plot, raw.traces, raw.layout, raw.config);
    });
});

function drawGraph($div, traces, layout, config, post_process = () => {}, register_event = false) {

    traces.forEach(function(trace, idx) {
        if('data_source' in trace) {
            let { data_source, ...trace_options } = trace;
            let event_name = `data-filter-${data_source.name}`;
            if(register_event) {
                window.addEventListener(event_name, function(e) {
                    console.log("received event: " + event_name);
                    drawGraph($div, traces, layout, config, post_process);
                });
            }
            $.post(
                BASE_PATH + '/request/AjaxDataSource/get_data_table',
                { name: data_source.name, single_user: data_source.single_user },
                function(data) {
                    if(data.success)
                    {
                        let keys = {};
                        if('cb' in data_source)
                            keys = data_source.cb(data.data);
                        else
                            keys = trace_cb(data.data, data_source);

                        if(idx === 0) {
                            Plotly.newPlot($div[0], [], layout, config);
                        }
                        Plotly.addTraces($div[0], {
                            ...trace_options,
                            ...keys
                        });

                        post_process();
                    }
                    else {
                        console.log(data);
                    }
                },
                'json'
            );
        } else {
            if(idx === 0) {
                Plotly.newPlot($div[0], [], layout, config);
            }
            Plotly.addTraces($div[0], trace);
        }
    });

}

function trace_cb(data, data_source) {
    let trace = {};
    for(let key in data_source.map) {
        trace[key] = [];
        data.forEach(function(item) {
            trace[key].push(item[data_source.map[key]])
        });
    }
    return trace;
}


function parseGraphData($data) {
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
