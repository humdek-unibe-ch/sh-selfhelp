$(document).ready(() => {

    var raw = parseGraphData($('div.graph-sankey'));
    console.log(raw.node);

    data = {
        type: "sankey",
        arrangement: "snap",
        link: raw.link,
        node: raw.node
    }

    var layout = {
        title: 'Test Sankey',
        showlegend: true
    };

    var config = {responsive: true};

    Plotly.newPlot('graph-sankey-plot', [data], layout, config);

});

function parseGraphData($root) {
    var j_data = $root.children('div.graph-sankey-data:first').text();
    try {
        return JSON.parse(j_data);
    }
    catch (e) {
        console.log(e);
        return {};
    }
}
