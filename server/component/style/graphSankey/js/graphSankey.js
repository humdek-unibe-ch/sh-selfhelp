$(document).ready(() => {

    $('div.graph-sankey').each(function () {
        var raw = parseGraphData($(this));
        data = {
            type: "sankey",
            arrangement: "snap",
            link: raw.link,
            node: raw.node
        }

        var layout = {
            title: raw.title,
            annotations: raw.annotations
        };

        var config = {responsive: true};

        Plotly.newPlot('graph-sankey-plot-' + raw.name, [data], layout, config);
        if(!raw.has_node_labels) {
            $(this).find('text.node-label').remove();
        }
    });
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
