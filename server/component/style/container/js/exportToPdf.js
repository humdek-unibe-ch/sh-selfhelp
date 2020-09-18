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
    $(pdfHolder).addClass('pdfA4');
    var children = $(container).children();
    for (const child of children) {
        if ($(child).find('#pdfExportHolder').length !== 0 || $(child).is('#pdfExportHolder')) {
            //skip PDF expot button
        } else if ($(child).find('.graph-base').length !== 0 || $(child).hasClass('graph-base')) {
            // graph. Convert to image then add to pdf
            loadingImage = true;
            var graphs = $(child).find('.graph-plot');
            for (const graph of graphs) {
                var imgUrl = await Plotly.toImage(graph, { format: 'png', width: $(graph).width(), height: $(graph).height() }).then(function (dataUrl) {
                    return dataUrl;
                });
                var img = $('<img>');
                img.attr('src', imgUrl);
                // set max widht of the image to 100% in order to fit in A4 format
                $(img).addClass('pdfA4Img');
                $(pdfHolder).append(img);
            }
        } else {
            // HTML element 
            $(child).find("img").each(function () {
                // set max widht of the image to 100% in order to fit in A4 format
                $(this).addClass('pdfA4Img');
            });
            var clone = child.cloneNode(true);
            $(clone).addClass('pdfA4');
            $(clone).children().addClass('pdfA4');
            $(pdfHolder).append(clone);
        }
    }
    // add the div that we want to convert to PDF in the DOM. It is hidden but we need it for the proper CSS
    $(container).append(pdfHolder);
    generatePdf(pdfHolder);
}

function generatePdf(el) {
    var skipPDFClass = '.skipPDF';
    var pdf = new jsPDF('p', 'pt', 'a4');
    var pdfName = 'Download.pdf';
    // search for skipPDF class and if it assinged to element we remove it as we do not want it in the PDF file
    $(el).find(skipPDFClass).each(function () {
        $(this).remove();
    });

    var options = {
        pagesplit: true,
    };

    pdf.fromHTML(el, 20, 20, options, function () {
        pdf.save(pdfName);
        // once the element is printed we remove it from the DOM
        $(el).remove();
    });
}