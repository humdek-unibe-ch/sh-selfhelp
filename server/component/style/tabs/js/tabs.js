$(document).ready(function() {
    $('.tabs-container').find('.tab-content').each(function() {
        $(this).closest('.tabs-container').append($(this));
    })
    $('button.tab-button').click(function() {
        var selector = '.tab-content-index-' + $(this).attr('data-context');
        var $tab_container = $(this).closest('.tabs-container').children(selector);
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
        $(this).closest('.tabs-container').children('.tab-content').not(selector).hide();
    });
});
