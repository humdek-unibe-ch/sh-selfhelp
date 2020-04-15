$(document).ready(() => {
    $('div.graph-sankey').each(function () {
        let $plot = $(this).children('div.graph-plot');
        var raw = parseGraphData($(this).children('div.graph-data:first'));
        var opts = parseGraphData($(this).children('div.graph-opts:first'));
        if(raw === null) return;

        // draw precomputation
        Plotly.newPlot($plot[0], [opts.pre_computation], raw.layout, raw.config);
        graph_sankey_postprocess_graph(opts, $plot);

        // recompute dynamically
        raw.traces[0].data_source.cb = function(data) {
            let res = graph_sankey_cb(data, opts);
            return res;
        };

        // draw dynamic data
        drawGraph($plot, raw.traces, raw.layout, raw.config, () => {
            graph_sankey_postprocess_graph(opts, $plot);
        }, true);
    });
});

function graph_sankey_compute_link_sum(data, source, target) {
    let sum = 0;
    data.forEach(function(row) {
        if(row[source.col.key] == source.type.key
                && row[target.col.key] == target.type.key) {
            sum++;
        }
    });
    return sum;
}

function graph_sankey_compute_node_position(size, idx) {
    let delta_fix = 0.00001;
    let pos = idx/(size - 1) + delta_fix;
    if(pos > 1) pos = 1 - delta_fix;
    return pos;
}

function graph_sankey_create_col_node(cols, idx) {
    return {
        idx: idx,
        key: cols[idx].key,
        label: cols[idx].label
    };
}

function graph_sankey_create_type_node(type, idx) {
    return {
        idx: idx,
        key: type.key,
        label: type.label,
        color: type.color ? type.color : "",
    };
}

function graph_sankey_prepare_transitions(cols) {
    let transitions = [];
    for(var i = 1; i < cols.length; i++) {
        transitions.push({
            source: graph_sankey_create_col_node(cols, i - 1),
            target: graph_sankey_create_col_node(cols, i)
        });
    }
    return transitions;
}

function graph_sankey_postprocess_graph(opts, $plot) {
    if(!opts.has_node_labels) {
        $plot.find('text.node-label').remove();
    }
}

function graph_sankey_cb(data, opts) {
    let cols = opts.cols;
    let types = opts.types;
    let transitions = graph_sankey_prepare_transitions(cols);
    let nodes_ref = {};
    let node_idx = 0;
    let links = {
        source: [],
        target: [],
        value: [],
        color: opts.link_color === "" ? null : []
    };
    transitions.forEach(function(transition) {
        types.forEach(function(type_src, idx_src) {
            types.forEach(function(type_tgt, idx_tgt) {
                let source = {
                    col: transition.source,
                    type: graph_sankey_create_type_node(type_src, idx_src)
                };
                let target = {
                    col: transition.target,
                    type: graph_sankey_create_type_node(type_tgt, idx_tgt)
                };
                let source_key = source.col.key + '-' + source.type.key;
                let target_key = target.col.key + '-' + target.type.key;
                let sum = graph_sankey_compute_link_sum(data, source, target);
                let color = "";
                if(sum >= opts.min) {
                    links.value.push(sum);
                    if(!(source_key in nodes_ref)) {
                        source.idx = node_idx;
                        nodes_ref[source_key] = source;
                        node_idx++;
                    }
                    links.source.push(nodes_ref[source_key].idx);

                    if(!(target_key in nodes_ref)) {
                        target.idx = node_idx;
                        nodes_ref[target_key] = target;
                        node_idx++;
                    }
                    links.target.push(nodes_ref[target_key].idx);

                    if(opts.link_color === "source"
                            && source.type.color !== "") {
                        color = source.type.color;
                    } else if(opts.link_color === "target"
                            && target.type.color !== "") {
                        color = target.type.color;
                    } else {
                        color = opts.link_color;
                    }
                    if(color !== "") {
                        links.color.push(color);
                    }
                }
            });
        });
    });
    if(links.color) {
        for(let i = 0; i < links.value.length; i++) {
            let alpha = Math.trunc(255 * opts.link_alpha);
            if(alpha < 0) alpha = 0;
            if(alpha > 255) alpha = 255;
            let hex = alpha.toString(16);
            if(hex.length === 1) hex = `0${hex}`;
            if(links.color[i]) {
                links.color[i] += hex;
            }
        }
    }
    links.hovertemplate = opts.link_hovertemplate

    // Count the number of nodes per column which will be displayed.
    // This is used to compute the node position on the y axis.
    let col_counts = {};
    cols.forEach(function(col, idx) {
        col_counts[col.key] = [];
    });
    for(let key in nodes_ref) {
        let node_ref = nodes_ref[key];
        col_counts[node_ref.col.key].push(node_ref.type.idx);
    }
    for(let key in col_counts) {
        col_counts[key].sort((a, b) => a - b);
    }

    // prepare nodes
    let nodes = {
        label: [],
        color: [],
        x: [],
        y: []
    }
    for(let key in nodes_ref) {
        let node_ref = nodes_ref[key];
        nodes.label[node_ref.idx] = node_ref.type.label;
        nodes.color[node_ref.idx] = node_ref.type.color;
        if(opts.is_grouped) {
            nodes.x[node_ref.idx] = graph_sankey_compute_node_position(
                cols.length, node_ref.col.idx);
            let y_idx = col_counts[node_ref.col.key].indexOf(node_ref.type.idx);
            nodes.y[node_ref.idx] = graph_sankey_compute_node_position(
                col_counts[node_ref.col.key].length, y_idx);
        }
    }
    nodes.hovertemplate = opts.node_hovertemplate
    return {
        link: links,
        node: nodes
    }
}
