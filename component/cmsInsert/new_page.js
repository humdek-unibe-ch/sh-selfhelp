$(document).ready(function() {
    var $url_input = $("input[name='url']");
    var $url;
    $('input[name="url-manual"]').change(function() {
        if($url_input.prop("readonly"))
            $url_input.prop("readonly", false);
        else
            $url_input.prop("readonly", true);
    });
    $('input[name="keyword"]').change(function() {
        $url = "/" + $(this).val();
        $url_input.val($url);
    });
    $('input[name="type"]').change(function() {
        if($(this).val() == 4)
            $url_input.val($url + "/[i:id]");
        else
            $url_input.val($url);
    });
});
