$(document).ready(function() {
    $('button.tab-button').click(function() {
        var $tab_container = $(this).parents("div.tab-button-list:first").next();
        $tab_container.html($(this).next().html());
        if($(this).hasClass("active"))
        {
            $(this).removeClass("active");
            $tab_container.slideUp("fast");
        }
        else
        {
            $(this).addClass("active");
            $tab_container.slideDown("fast");
        }
        $('button.tab-button').not($(this)).removeClass("active");
    });
});
