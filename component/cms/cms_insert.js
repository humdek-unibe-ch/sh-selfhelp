$(document).ready(function() {
    $('span[id|="global-sections"').click(function() {
        var ids = $(this).attr('id').split('-');
        $('span[id|="global-sections"').removeClass("active");
        $(this).addClass("active");
        $('input[name="section-name"]').val($(this).text());
        $('input[name="add-section-link"]').val(ids[ids.length - 2]);
        $('input[name="new-section"]')
            .prop("checked", false)
            .attr("disabled", true);
        console.log(ids);
        $('select[name="section-style"]').val(ids[ids.length - 1]);
    });
    $('input[name="section-name"]').keyup(function(){
        $('input[name="new-section"]')
            .prop("checked", true)
            .attr("disabled", true);
        $('span[id|="global-sections"').removeClass("active");
        $('input[name="add-section-link"]').val("");
    });
});
