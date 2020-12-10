$(document).ready(function() {
    function activate($this) {
        var selector = '.tab-content-index-' + $this.attr('data-context');
        var $tab_container = $this.closest('.tabs-container').children(selector);
        var $buttons = $this.closest('.tabs-container').children('button.tab-button');
        if($this.hasClass("active"))
        {
            $this.removeClass("active");
            $tab_container.slideUp("fast");
        }
        else
        {
            $this.addClass("active");
            $tab_container.slideDown("fast");
        }
        $buttons.not($this).removeClass("active");
        $this.closest('.tabs-container').children('.tab-content').not(selector).hide();
    }
    $('.tabs-container').find('.tab-content').each(function() {
        $(this).closest('.tabs-container').append($(this));
    });
    $('button.tab-button.style-' + location.hash.substring(1)).each(function() {
        activate($(this));
    });
    $('button.tab-button').click(function() {
        activate($(this));
    });
});
