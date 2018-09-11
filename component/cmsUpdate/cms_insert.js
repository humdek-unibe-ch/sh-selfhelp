$(document).ready(function() {
    var name_prefix = "";
    var name_postfix = "";
    var $input_name = $('input[name="section-name"]');
    var $input_name_prefix = $('input[name="section-name-prefix"]');
    var $input_style = $('select[name="section-style"]');
    var $input_new_section = $('input[name="new-section"]');
    var $input_add_section_link = $('input[name="add-section-link"]');

    var $search_elements = $('span[id|="sections-search"');
    $search_elements.click(function() {
        var ids = $(this).attr('id').split('-');
        $search_elements.removeClass("active");
        $(this).addClass("active");

        $input_name.val($(this).text());
        $input_add_section_link.val(ids[ids.length - 2]);
        $input_new_section.prop("checked", false);
        $input_style.val(ids[ids.length - 1]);
        name_postfix = $('select[name="section-style"] option:selected').text().trim();
        $input_name_prefix.val("");

        $input_name.prop("disabled", true);
        $input_name_prefix.prop("disabled", true);
        $input_style.prop("disabled", true);
        $input_new_section.prop("disabled", false);
    });
    $input_name_prefix.keyup(function() {
        name_prefix = $(this).val();
        $input_name.val(name_prefix + "-" + name_postfix);
    });
    $input_style.change(function() {
        name_postfix = $('select[name="section-style"] option:selected').text().trim();
        $input_name.val(name_prefix + "-" + name_postfix);
    });
    $input_new_section.click(function() {
        $search_elements.removeClass("active");
        $input_name.prop("disabled", false);
        $input_name_prefix.prop("disabled", false);
        $input_style.prop("disabled", false);
        $input_new_section.prop("disabled", true);
        $input_add_section_link.val("");
    });
});
