$(document).ready(() => {
    $('div.graph-base').each(function () {
        let $plot = $(this).children('div.graph-plot:first');
        var raw = parseGraphData($(this).children('div.graph-data:first'));
        if(raw === null) return;

        let traces = drawGraph($plot, raw.traces, raw.layout, raw.config, () => {}, true);
        new ResizeSensor($plot, function() {
            Plotly.newPlot($plot[0], traces, raw.layout, raw.config);
        });
    });
});

function drawGraph($div, traces, layout, config, post_process = () => {}, register_event = false) {
    let $pending = $div.prev();
    let busy_count = 0;
    // let date = new Date();
    // let now = date.getTime();
    let traces_cache = [];
    traces.forEach(function(trace, idx) {
        if('data_source' in trace) {
            let { data_source, ...trace_options } = trace;
            let event_name = `data-filter-${data_source.name}`;

            $pending.removeClass('d-none');
            busy_count++;
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
                            keys = graphTraceCb(data.data, data_source);

                        if(idx === 0) {
                            Plotly.newPlot($div[0], [], layout, config);
                        }
                        traces_cache.push(deepmerge(trace_options, keys));
                        Plotly.addTraces($div[0], traces_cache[idx]);

                        post_process();
                        busy_count--;
                        if(busy_count === 0) {
                            $pending.addClass('d-none');
                            // let date = new Date();
                            // console.log(date.getTime() - now);
                        }
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
            traces_cache.push(trace);
            Plotly.addTraces($div[0], trace);
        }
    });
    return traces_cache;
}

/**
 * The default callback function to be appliad on a trace data set.
 *
 * @param array data
 *  An array of rows where each row is an object with key value pairs where the
 *  key is the column name.
 * @param object data_source
 *  This object allow sto define how the data from parameter `data` is assigned
 *  to trace keys. The object has the following keys:
 *  - `name<string>`: the name of the data source table as stored in the DB
 *  - `map<object>`: the association of the data to plotly trace keys. This is
 *    an object where each key is a dot-seperated string which will produce an
 *    element inside the trace object accordingly. The value of a map item can
 *    be of the following types:
 *     - `string`: indicating the data column to be used as values
 *     - `array`: an array of operand objects which allow to perform simple
 *       operations on data columns and use the result as values. An operand
 *       object must have the following keys:
 *        - `name<string>`: the name of the data column
 *        - `op<string>`: one of the following mathematical operations:
 *          - `max`: returns the maximal value of the data column.
 *          - `min`: returns the minimal value of the data column.
 *          - `sum`: returns the sum of all values of the data column.
 *          - `avg`: returns the average of all values of the data column.
 *     - `object`: an object definig how to group values of one data column and
 *       how to assign labels to each group. The object must have the following
 *       keys:
 *       - `name<string>`: the name of the data column.
 *       - `op<string>`: one of the following operations to produce grouping
 *         values:
 *         - `count`: count the occurences of each individual value
 *         - `percent`: count the occurences of each individual value and
 *           divide the result by the rouw count
 *         - `sum`: accumulate distinct values (i.e. count * value)
 *       - `labels<object>`: allows to assign labels to each value. The
 *         following keys are expected:
 *         - `key<string>`: a dot-seperated string which will produce an
 *           element inside the trace object accordingly.
 *         - `map<object>`: a list of key-value pairs where the key corresponds
 *           to the data value to be labelled and the value is a label string.
 *       - `factor<number>`: If defined, this number will be multiplied to each
 *         value computed with the function defined above.
 *       - `offset<number>`: If defined, this number will be added to each
 *         value computed with the function defined above (after multiplying by
 *         `factor`).
 * @return
 *  A partial plotly trace objects with keys set as defined in param
 *  `data_source`.
 */
function graphTraceCb(data, data_source) {
    let trace = {};
    for(let key in data_source.map) {
        graphExpandDotString(trace, key, []);
        let source = data_source.map[key];
        let trace_key = key.split('.').reduce((o, i) => o[i], trace);
        if(Array.isArray(source)) {
            // column operations
            source.forEach(function(item) {
                let val = 0;
                if(item.op === "max") {
                    val = Math.max.apply(Math,
                        data.map(function(o) {return o[item.name]}));
                } else if (item.op === "min") {
                    val = Math.min.apply(Math,
                        data.map(function(o) {return o[item.name]}));
                } else if (item.op === "sum") {
                    val = data.reduce((a, b) => {
                        return a + Number(b[item.name])
                    }, 0);
                } else if (item.op === "avg") {
                    val = data.reduce((a, b) => {
                        return a + Number(b[item.name])
                    }, 0);
                    val /= data.length;
                }
                trace_key.push(val);
            });
        } else if(typeof source === 'object' && source !== null) {
            let vals = {};
            let trace_opt_keys = {};
            for(let key in source.options) {
                trace_opt_keys[key] = graphExpandDotString(trace, key, []);
                // trace_opt_keys[key] = key.split('.').reduce((o, i) => o[i], trace);
            }
            data.forEach(function(item) {
                let val = item[source.name];
                if(!(val in vals)) {
                    vals[val] = trace_key.length;
                    trace_key.push(0);
                    for(let key in source.options) {
                        trace_opt_keys[key].push(source.options[key][val]);
                    }
                }
                if(source.op === "count" || source.op === "percent") {
                    trace_key[vals[val]]++;
                } else if(source.op === "sum") {
                    trace_key[vals[val]] += val;
                }
            });
            if(source.op === "percent") {
                for(let idx = 0; idx < trace_key.length; idx++) {
                    trace_key[idx] /= data.length;
                }
            }
            if('factor' in source) {
                for(let idx = 0; idx < trace_key.length; idx++) {
                    trace_key[idx] *= source.factor;
                }
            }
            if('offset' in source) {
                for(let idx = 0; idx < trace_key.length; idx++) {
                    trace_key[idx] += source.offset;
                }
            }
        } else {
            data.forEach(function(item) {
                trace_key.push(item[source])
            });
        }
    }
    return trace;
}

function graphExpandDotString(obj, str, value) {
    var items = str.split('.');
    var ref = obj;

    //  loop through all nodes, except the last one
    for(var i = 0; i < items.length - 1; i ++)
    {
        ref[items[i]] = {};
        ref = ref[items[i]]; // shift the reference to the newly created object
    }

    ref = ref[items[items.length - 1]] = value; // apply the final value
    return ref;
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
