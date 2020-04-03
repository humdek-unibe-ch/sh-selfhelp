$(document).ready(() => {
    $('div.graph-base').each(function () {
        let $plot = $(this).children('div.graph-plot');
        var raw = parseGraphData($(this).children('div.graph-data:first'));
        if(raw === null) return;

        Plotly.newPlot($plot[0], raw.data, raw.layout, raw.config);
    });
});

function drawGraph($div, data, layout, config) {
    Plotly.newPlot($div[0], [], layout, config);

    data_in.forEach(function(trace) {
        let { data_source, ...trace_options } = trace;
        $.post(
            BASE_PATH + '/request/AjaxDataSource/get_data',
            { name: data_source.name, single_user: data_soutce.single_user },
            function(data) {
                if(data.success)
                {
                    Plotly.addTraces($div[0], {
                        ...trace_options,
                        ...data_source.cb(data.data)
                    });
                }
                else {
                    console.log(data);
                }
            },
            'json'
        );
    });

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
