$(document).ready(() => {
    initGraph();
});

function initGraph(){
    $('div.graph-base').each(function () {
        let $plot = $(this).children('div.graph-plot:first');
        var raw = parseGraphData($(this).children('div.graph-data:first'));
        if(raw === null) return;

        let {traces, count} = drawGraph($plot, raw.traces, raw.layout, raw.config, () => {}, true);
        new ResizeSensor($plot, function() {
            if(count === raw.traces.length) {
                Plotly.newPlot($plot[0], traces, raw.layout, raw.config);
            }
        });
    });
}

/**
 * Draw a plotly graph
 *
 * @param jObject $div
 *  The jquery object pointing to the graph div
 * @param array traces
 *  An array of trace objects. These objects can hold any option as defined in
 *  the Plotly.js documentation. 
 */
function drawGraph($div, traces, layout, config, post_process = () => {}, register_event = false) {
    let $pending = $div.prev();
    let busy_count = 0;
    // let date = new Date();
    // let now = date.getTime();
    let traces_cache = [];
    let count = 0;
    let events = [];

    Plotly.newPlot($div[0], [], layout, config);
    traces.forEach(function(trace, idx) {
        if('data_source' in trace) {
            let { data_source, ...trace_options } = trace;
            let event_name = `data-filter-${data_source.name}`;

            $pending.removeClass('d-none');
            busy_count++;
            if(register_event && !events.includes(event_name)) {
                events.push(event_name);
                window.addEventListener(event_name, function(e) {
                    console.log("received event: " + event_name);
                    drawGraph($div, traces, layout, config, post_process);
                });
            }
            var urlAjaxRequest = BASE_PATH + '/request/AjaxDataSource/get_data_table/' + data_source.name;
            $.post(
                urlAjaxRequest,
                {
                    single_user: data_source.single_user ? data_source.single_user : false // not important after the changes
                },
                function(data) {
                    if(data.success)
                    {
                        let keys = {};
                        if('cb' in data_source)
                            keys = data_source.cb(data.data);
                        else
                            keys = graphTraceCb(data.data, data_source, idx);

                        traces_cache[idx] = deepmerge(trace_options, keys);
                        Plotly.addTraces($div[0], traces_cache[idx]);
                        if(count > idx) {
                            // data did not arrive in order, reordering traces
                            Plotly.moveTraces($div[0], count, idx);
                        }
                        count++;

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
            traces_cache[idx] = trace;
            count++;
            Plotly.addTraces($div[0], trace, idx);
        }
    });
    return {
        count: count,
        traces: traces_cache
    };
}

/**
 * The default callback function to be appliad on a trace data set.
 *
 * @param array data
 *  An array of rows where each row is an object with key value pairs where the
 *  key is the column name.
 * @param object data_source
 *  This object allows to define how the data from parameter `data` is assigned
 *  to trace keys. The object has the following keys:
 *  - `name<string>`: the name of the data source table as stored in the DB
 *  - `map<object>`: the association of the data to plotly trace keys. This is
 *    an object where each key is a dot-seperated string which will produce an
 *    element inside the trace object accordingly. The value of a map item can
 *    be of the following types:
 *     - `string`: indicating the data column to be used as values
 *     - `array`: an array of operand objects which allow to perform simple
 *       operations on data columns and use the result as values.
 *       An operand object must have the following keys:
 *        - `name<string>`: the name of the data column
 *        - `op<string>`: one of the following mathematical operations:
 *          - `max`: returns the maximal value of the data column.
 *          - `min`: returns the minimal value of the data column.
 *          - `sum`: returns the sum of all values of the data column.
 *          - `avg`: returns the average of all values of the data column.
 *     - `object`: an object definig how to group values of one data column and
 *       how to assign labels to each group. Refer to graphTraceCbData() for
 *       more information on the available object keys.
 * @return
 *  A partial plotly trace objects with keys set as defined in param
 *  `data_source`.
 */
function graphTraceCb(data, data_source) {
    let trace = {};
    for(let key in data_source.map) {
        graphExpandDotString(trace, key, []);
        let source = data_source.map[key];
        let trace_key = graphExpandDotString(trace, key, []);
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
            graphTraceCbData(data, trace, trace_key, source.name, source.ignore, source.options, source.order, source.maps, source.children);
        } else {
            data.forEach(function(item) {
                trace_key.push(item[source])
            });
        }
    }
    return trace;
}

/**
 * This function handles the data processing of the data map value is an object.
 * When indicated, the function parameter corresponds to a data map object key.
 *
 * @param array data
 *  An array of rows where each row is an object with key value pairs where the
 *  key is the column name.
 * @param object trace
 *  The current graph trace wto work on.
 * @param string trace_key
 *  The key of the trace as a dot-notation.
 * @param string name
 *  Corresponds to the object map key `name`. It defines the name of the data
 *  column.
 * @param array ignore
 *  Corresponds to the object map key `ignore`. It defines a list of value
 *  types to ignore when computing the trace.
 * @param object options
 *  Corresponds to the object map key `options`. It allows to define
 *  post-process operations on the trace data. The following keys are available:
 *   - `op<string>`: one of the following operations to produce grouping
 *     values:
 *      - `count`: count the occurences of each individual value
 *      - `idx`: increment per new value type
 *      - `percent`: count the occurences of each individual value and
 *        divide the result by the rouw count
 *      - `sum`: accumulate distinct values (i.e. count * value)
 *      - `val`: use the value types
 *   - `range<object>`: allows to define a range to which the trace values will
 *     be evenly distributed. The range object must contain the keys `min`
 *     and `max` to define the minimum and maximum range value, respectively.
 *   - `factor<number>`: If defined, this number will be multiplied to each
 *     value computed with the function defined in `op`.
 *   - `offset<number>`: If defined, this number will be added to each
 *     value computed with the function defined in `op` (after multiplying by
 *     `factor`).
 *   - `round<number>`: Defines the number of digits after the comma to round
 *     to. 0 means rounding to an integer.
 *   - `suffix<string>`: Allows to add a suffix to each value.
 * @param mixed order
 *  Corresponds to the object map key `order`. This defines the trace order and
 *  can be of the following types:
 *   - `string`: either "asc" or "desc" to order the traces either by
 *     ascending values or by descending values, respectively
 *   - `array`: an array of indices indication the new position of the
 *     traces.
 *   - `object`: an object with the key `vals` which holds an ordered
 *     list of value types by which the traces will be ordered.
 * @param object maps
 *  Corresponds to the object map key `map`. This allows to map static data to
 *  predefined keys by respecting the post-process options defined above. The
 *  keys of the object are dot-seperated strings which will produce an element
 *  inside the trace object accordingly. The values are map objects which will
 *  map static data to a value type. To use this for labelling value types, the
 *  key would be the value type and the value the label to assign to the value
 *  type.
 * @param object children
 *  Corresponds to the object map key `children`. This allows to map furter
 *  data items from the data table to trace keys by respecting the post-process
 *  options defined above. Children are defined by a value-key pair where the
 *  key is a dot-seperated string which will produce an element inside the
 *  trace object accordingly and the value is an option object as defined by
 *  the parameter `options`.
 */
function graphTraceCbData(data, trace, trace_key, name, ignore, options, order, maps = {}, children = {}) {
    let vals = {};
    let trace_opt_keys = {};
    for(let key in maps) {
        trace_opt_keys[key] = graphExpandDotString(trace, key, []);
        // trace_opt_keys[key] = key.split('.').reduce((o, i) => o[i], trace);
    }
    let new_val = false;
    let idx = 0;
    let has_order = false;
    let sort_indices = null;
    if(typeof order === 'object' && order !== null && 'vals' in order) {
        has_order = true;
    }
    data.forEach(function(item) {
        let val = item[name];
        if(ignore && ignore.includes(val)) {
            return;
        }
        if(!(val in vals)) {
            new_val = true;
            if(has_order) {
                vals[val] = order.vals.indexOf(Number(val));
            } else {
                vals[val] = trace_key.length;
            }
            trace_key[vals[val]] = 0;
            for(let key in maps) {
                trace_opt_keys[key][vals[val]] = maps[key][val];
            }
        }
        if(options.op === "count" || options.op === "percent") {
            trace_key[vals[val]]++;
        } else if(options.op === "sum") {
            trace_key[vals[val]] += val;
        } else if(options.op === "val") {
            trace_key[vals[val]] = Number(val);
        } else if(options.op === "idx") {
            if(new_val) {
                trace_key[vals[val]] = idx++;
                new_val = false;
            }
        }
    });
    if(options.op === "percent") {
        for(let idx = 0; idx < trace_key.length; idx++) {
            trace_key[idx] /= data.length;
        }
    }
    if('range' in options && 'min' in options.range
            && 'max' in options.range) {
        let max = Math.max(...trace_key);
        let min = Math.min(...trace_key);
        for(let idx = 0; idx < trace_key.length; idx++) {
            trace_key[idx] = (trace_key[idx] - min)
                * (options.range.max - options.range.min)
                / (max - min) + options.range.min;
        }
    }
    if('factor' in options) {
        for(let idx = 0; idx < trace_key.length; idx++) {
            trace_key[idx] *= options.factor;
        }
    }
    if('offset' in options) {
        for(let idx = 0; idx < trace_key.length; idx++) {
            trace_key[idx] += options.offset;
        }
    }
    if('round' in options) {
        for(let idx = 0; idx < trace_key.length; idx++) {
            let digit = Math.pow(10, options.round)
            trace_key[idx] = Math.round((trace_key[idx]
                + Number.EPSILON) * digit) / digit;
        }
    }
    if(Array.isArray(order)) {
        if(options.op !== "idx") {
            // order by indices but not if its the idx operation
            let tmp = trace_key.slice();
            for(let i = 0; i < tmp.length; i++) {
                trace_key[i] = tmp[order[i]];
            }
        }
    } else if (order && typeof order === "string") {
        if(order === "asc") {
            sort_indices = sortWithIndices(trace_key);
        } else if(order === "desc") {
            sort_indices = sortWithIndices(trace_key, false);
        }
        if(sort_indices) {
            for(let key in trace_opt_keys) {
                let tmp = trace_opt_keys[key].slice();
                for(let i = 0; i < tmp.length; i++) {
                    trace_opt_keys[key][i] = tmp[sort_indices[i]];
                }
            }
        }
    }
    if('suffix' in options) {
        for(let idx = 0; idx < trace_key.length; idx++) {
            trace_key[idx] = `${trace_key[idx]}${options.suffix}`
        }
    }
    for(let child_key in children) {
        let trace_key_child = graphExpandDotString(trace, child_key, []);
        graphTraceCbData(data, trace, trace_key_child, name, ignore, children[child_key], sort_indices);
    }
}

/**
 * Sorts an array and provides an array which indicates the sort pattern
 *
 * @param array toSort
 *  The array to be sorted
 * @param boolean asc
 *  True if sorting should be ascending, False if sorting should be descending
 * @return
 *  An array which indicates to new index of each item in the original array.
 */
function sortWithIndices(toSort, asc = true) {
    let sortIndices = [];
    let sign = asc ? 1 : -1;
    for (var i = 0; i < toSort.length; i++) {
        toSort[i] = [toSort[i], i];
    }
    toSort.sort(function(left, right) {
        return left[0] < right[0] ? sign * -1 : sign * 1;
    });
    for (var j = 0; j < toSort.length; j++) {
        sortIndices.push(toSort[j][1]);
        toSort[j] = toSort[j][0];
    }
    return sortIndices;
}

/**
 * Allows to expand a dot-string into an element reference of an object.
 *
 * @param object obj
 *  The object to parse for the dot-string
 * @param string str
 *  The dot-string
 * @param mixed value
 *  The default value to assign to the object element
 * @return
 *  A reference to the object element.
 */
function graphExpandDotString(obj, str, value) {
    var items = str.split('.');
    var ref = obj;

    //  loop through all nodes, except the last one
    for(var i = 0; i < items.length - 1; i ++)
    {
        if(!(items[i] in ref)) {
            ref[items[i]] = {};
        }
        ref = ref[items[i]]; // shift the reference to the newly created object
    }

    ref = ref[items[items.length - 1]] = value; // apply the final value
    return ref;
}

/**
 * Parse the JSON data definig a graph. On failure the function will log the
 * error message to the console.
 *
 * @param jObject $data
 *  A reference to a jQuery object pointing to the HTML element holding the
 *  JSON string.
 * @return
 *  The JSON object on success or null on failure.
 */
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
