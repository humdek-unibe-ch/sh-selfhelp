$(document).ready(() => {
    $('#pdfExport').click(() => {
        exportPageToPDF();
    });
});

// get all children that we want to export and put them in a new div with A4 format. We do not assign the div in the dom. We just use it to create the PDF
async function exportPageToPDF() {
    // get container. We will need all its children to export them to PDF except the PDF export button
    var container = $('#pdfExportHolder').parent();
    var pdfHolder = document.createElement('div');
    $(pdfHolder).addClass('pdfHolder');
    var children = $(container).children();
    for (const child of children) {
        if ($(child).find('#pdfExportHolder').length !== 0 || $(child).is('#pdfExportHolder')) {
            //skip PDF expot button
        } else if ($(child).find('.graph-base').length !== 0 || $(child).hasClass('graph-base')) {
            // graph. Convert to image then add to pdf
            var graphs = $(child).find('.graph-plot');
            for (const graph of graphs) {
                var imgUrl = await Plotly.toImage(graph, { format: 'png', width: 930, height: 450 }).then(function (dataUrl) {
                    return dataUrl;
                });
                var img = $('<img>');
                img.attr('src', imgUrl);
                // set max widht of the image to 100% in order to fit in A4 format
                $(img).addClass('pdfA4');
                $(pdfHolder).append(img);
            }
        } else {
            // HTML element 
            $(child).find("img").each(function () {
                // set max widht of the image to 100% in order to fit in A4 format
                $(this).addClass('pdfA4');
            });
            var clone = child.cloneNode(true);
            $(clone).addClass('pdfA4');
            $(clone).children().addClass('pdfA4');
            $(pdfHolder).append(clone);
        }
    }
    generatePDF(pdfHolder);
}

function generatePDF(element) {
    var pdf = new jspdf.jsPDF('p', 'pt', 'A4'); // init PDF   
    pdf.html(
        element,
        {
            callback: function (doc) {
                doc.save('Download.pdf');                
            },
            x: 15,
            y: 15
        });
}