$(document).ready(() => {

    $('div.graph-sankey').each(function () {
        var raw = parseGraphData($(this));
        if(raw === null) return;
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

        var $plot = $(this).children('div.graph-sankey-plot-' + raw.name);
        Plotly.newPlot($plot[0], [data], layout, config);
        if(!raw.has_node_labels) {
            $plot.find('text.node-label').remove();
        }
    });
});

function parseGraphData($root) {
    var j_data = $root.children('div.graph-sankey-data:first').text();
    try {
        return JSON.parse(j_data);
    }
    catch (e) {
        console.log("cannot parse raw data of sankey graph");
        console.log(e);
        return null;
    }
}
