$(document).ready(function () {
    initTabs();
});

function initTabs() {
    if (window.location.href.includes('cms_update')) {
        // in edit mode do not initialize
        return;
    }
    $('.tabs-container').find('.tab-content').each(function () {
        $(this).closest('.tabs-container').append($(this));
    });    
    activate_with_hash();
    $(window).bind('hashchange', function (e) {
        activate_with_hash();
    });
    $('button.tab-button').click(function () {
        console.log($(this).attr('data-context'));
        window.location.hash = "#section-" + $(this).attr('data-context');
    });
    if (!window.location.hash) {
        // if not tabs is opened, select the first one
        var firstTab = $('button.tab-button').first();
        activate($('button.tab-button:not(.active):not(.no-anchor-expand).style-'
            + "section-" + $(firstTab).attr('data-context')));
    }
}

function activate($this) {    
    var selector = '.tab-content-index-' + $this.attr('data-context');
    var $tab_container = $this.closest('.tabs-container').children(selector);
    var $buttons = $this.closest('.tabs-container').children('button.tab-button');
    if ($this.hasClass("active")) {
        $this.removeClass("active");
        $tab_container.slideUp("fast");
    }
    else {
        $this.addClass("active");
        $tab_container.slideDown("fast");
    }
    $buttons.not($this).removeClass("active");
    $this.closest('.tabs-container').children('.tab-content').not(selector).hide();
}

function activate_with_hash() {
    activate($('button.tab-button:not(.active):not(.no-anchor-expand).style-'
        + window.location.hash.substring(1)));
}
