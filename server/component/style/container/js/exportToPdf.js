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
                var cloneGraph = graph.cloneNode(true);
                $(cloneGraph).addClass('graphToImg');
                $(container).append(cloneGraph);
                var imgUrl = await Plotly.toImage(graph, { format: 'png', width: $(cloneGraph).width(), height: $(cloneGraph).height() }).then(function (dataUrl) {
                    $(cloneGraph).remove();
                    return dataUrl;
                });
                var img = $('<img>');
                img.attr('src', imgUrl);
                // set max widht of the image to 100% in order to fit in A4 format
                $(img).addClass('pdfA4ImgGraph');
                $(pdfHolder).append(img);

            }
        } else {
            // HTML element 
            $(child).find("img").each(function () {
                // set max widht of the image to 100% in order to fit in A4 format
                $(this).addClass('pdfA4Img');
            });
            var clone = child.cloneNode(true);
            $(pdfHolder).append(clone);

        }
    }

    // keep columns don move them to rows even if the device width is small
    $(pdfHolder).find("[class*=col-3]").each(function () {
        $(this).addClass('pdfA4Col-3');
    });
    $(pdfHolder).find("[class*=col-sm-6]").each(function () {
        $(this).addClass('pdfA4Col-sm-6');
    });
    $(pdfHolder).find("[class*=col-sm-4]").each(function () {
        $(this).addClass('pdfA4Col-sm-4');
    });
    $(pdfHolder).find("[class*=col-sm]").each(function () {
        $(this).addClass('pdfA4Col-sm');
    });

    // $(container).append(pdfHolder); // use it when debug to adjust PDF A4 page
    htmlToPDF(pdfHolder);

}

function htmlToPDF(element) {
    var skipPDFClass = '.skipPDF';
    // search for skipPDF class and if it assinged to element we remove it as we do not want it in the PDF file
    $(element).find(skipPDFClass).each(function () {
        $(this).remove();
    })
    // add css classes for page break
    var opt = {
        margin: 20,
        filename: 'Download.pdf',
        pagebreak: { before: '.pdfStartNewPage', after: ['.pdfStartNewPageAfter'], avoid: 'img' },
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: {
            scale: 4,
            DPI: 600
        },
        jsPDF: { unit: 'pt', format: 'letter', orientation: 'portrait' }
    };

    // New Promise-based usage:
    html2pdf().set(opt).from(element).save();
}