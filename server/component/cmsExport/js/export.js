$(document).ready(function () {
    exportJson();
});

function exportJson() {
    const jsonExportData = $('#jsonExportData').val();
    const originalData = JSON.parse(jsonExportData);
    const a = document.createElement("a");
    a.href = URL.createObjectURL(new Blob([JSON.stringify(originalData, null, 2)], {
        type: "text/plain"
    }));
    a.setAttribute("download", originalData['file_name'] + ".json");
    $(a).addClass('d-none');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}