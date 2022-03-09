$(document).ready(function () {
    $('.book-turnjs').each(function () {
        var init = false;
        var config = $(this).data('config');
        if (!config || config == '""') {
            config = {}
        }
        var slider = false;
        if (config && config['slider']) {
            slider = true;
            setSlider();
        } else {
            $("#slider-bar").addClass('d-none');
        }
        config['when'] = {
            turning: function (e, page, pageObject) {
                if ($(this).turn('data').hover) {
                    $(this).turn('data').hover = false;
                    e.preventDefault();
                    return;
                }
                var book = $(this);
                var currentPage = book.turn('page');
                var pages = book.turn('pages');
                if (config['saveOnTurnPage']) {
                    for (let i = 0; i < page; i++) {
                        for (let j = 0; j < $(this).find('[page="' + i + '"]').find('input,textarea,select').filter('[required]').length; j++) {
                            const el = $(this).find('[page="' + i + '"]').find('input,textarea,select').filter('[required]')[j];
                            if ($(el).attr('type') == 'radio' && !$(this).find('[page="' + i + '"]').find('input[name="' + $(el).attr('name') + '"]:checked').val()) {
                                e.preventDefault();
                                book.turn('page', i);
                                el.reportValidity();
                                return;
                            } else {
                                if (!$(el).val()) {
                                    e.preventDefault();
                                    book.turn('page', i);
                                    el.reportValidity();
                                    return;
                                }
                            }
                        }
                    }
                }
                // when page is turned add the page number to the url
                window.location.hash = "#page-" + page;
                if (currentPage > 3 && currentPage < pages - 3) {
                    if (page == 1) {
                        book.turn('page', 2).turn('stop').turn('page', page);
                        e.preventDefault();
                        return;
                    } else if (page == pages) {
                        book.turn('page', pages - 1).turn('stop').turn('page', page);
                        e.preventDefault();
                        return;
                    }
                } else if (page > 3 && page < pages - 3) {
                    if (currentPage == 1) {
                        book.turn('page', 2).turn('stop').turn('page', page);
                        e.preventDefault();
                        return;
                    } else if (currentPage == pages) {
                        book.turn('page', pages - 1).turn('stop').turn('page', page);
                        e.preventDefault();
                        return;
                    }
                }
                updateDepth(book, page);
                if (page >= 2) {
                    $('.book-turnjs .p2').addClass('fixed');
                } else {
                    $('.book-turnjs .p2').removeClass('fixed');
                }
                if (page < book.turn('pages')) {
                    $('.book-turnjs .secondLast').addClass('fixed');
                } else {
                    $('.book-turnjs .secondLast').removeClass('fixed');
                }
                if (config['saveOnTurnPage']) {
                    // save forms inside book
                    var currentView = book.turn('view');
                    currentView.forEach(p => {
                        var submitBtn = $(this).find('[page="' + p + '"]').find(':submit');
                        $(submitBtn).attr("formnovalidate");
                        if (currentPage <= page) {
                            $(submitBtn).attr("formnovalidate", 'formnovalidate');
                        } else {
                            // if we go backwards we remove this and try to validate
                            $(submitBtn).removeAttr("formnovalidate");
                        }
                        $(this).find('[page="' + p + '"]').find(':submit').click();
                    });
                    // save form if it is parrent of the book
                    if (init) {
                        var submitBtn = $(this).parent().find("> :submit");
                        if (currentPage <= page) {
                            $(submitBtn).attr("formnovalidate", 'formnovalidate');
                        } else {
                            // if we go backwards we remove this and try to validate
                            $(submitBtn).removeAttr("formnovalidate");
                        }
                        $(this).parent().find("> :submit").click();
                    }
                }
                setButtonsVisibility(page, pages);
            },
            turned: function (e, page, view) {
                var book = $(this);
                if (page == 2 || page == 3) {
                    book.turn('peel', 'br');
                }
                updateDepth(book);
                if (slider) {
                    $('#slider').slider('value', getViewNumber(book, page));
                }
                book.turn('center');
            },
            end: function (e, pageObject) {
                var book = $(this);
                updateDepth(book);
                setTimeout(function () {
                    if (slider) {
                        $('#slider').slider('value', getViewNumber(book));
                    }
                }, 1);
            },
            missing: function (e, pages) {

            },
            start: function (e, pageObject, corner) {
                if (config['stopClickPageTurn'] && corner != null) {
                    $(this).turn('data').hover = true;
                    return e.preventDefault();
                }
            }
        }
        $(this).turn(config);
        setButtonsVisibility(pageNumber ? pageNumber : 1, $(this).turn('pages'));
        var book = $(this);
        // check if there is page number in the url
        var pageNumber = window.location.hash.substring(1);
        if (pageNumber) {
            $(this).turn('page', pageNumber.replace('page-', ''));
        } else {
            init = true;
        }
        if (slider) {
            $('#slider').slider('option', 'max', $(this).turn('pages') / 2 + 1);
        }
        $(this).addClass('animated');
        // Show canvas
        $('#canvas').css({ visibility: '' });
        if (config['showButtons']) {
            $('#book-buttons').removeClass('d-none');
            $('#book-buttons').addClass('d-flex');
            $('#book-previous-button').click(function (e) {
                book.turn('data').hover = false;
                e.preventDefault();
                book.turn("previous");
            });
            $('#book-next-button').click(function (e) {
                book.turn('data').hover = false;
                e.preventDefault();
                book.turn("next");
            });
        }
        setTimeout(() => {
            init = true; // workaround for page loading in the middle of the book
        }, 2000);
    });

});

function updateDepth(book, newPage) {
    var page = book.turn('page'),
        pages = book.turn('pages'),
        depthWidth = 16 * Math.min(1, page * 2 / pages);
    newPage = newPage || page;
    if (newPage > 3) {
        $('.book-turnjs .p2 .depth').css({
            width: depthWidth,
            left: 20 - depthWidth
        });
    } else {
        $('.book-turnjs .p2 .depth').css({ width: 0 });
    }
    depthWidth = 16 * Math.min(1, (pages - page) * 2 / pages);
    if (newPage < pages - 3) {
        $('.book-turnjs .secondLast .depth').css({
            width: depthWidth,
            right: 20 - depthWidth
        });
    } else {
        $('.book-turnjs .secondLast .depth').css({ width: 0 });
    }
}

function getViewNumber(book, page) {
    return parseInt((page || book.turn('page')) / 2 + 1, 10);
}

function setButtonsVisibility(page, pages) {
    if (page == 1) {
        $('#book-previous-button').addClass("invisible");
    } else {
        $('#book-previous-button').removeClass("invisible");
    }
    if (page == pages) {
        $('#book-next-button').addClass("invisible");
    } else {
        $('#book-next-button').removeClass("invisible");
    }
}

function setSlider() {
    // Slider
    $("#slider").slider({
        min: 1,
        max: 100,
        stop: function () {
            $('.book-turnjs').turn('page', Math.max(1, $(this).slider('value') * 2 - 2));
        }
    });
}

// Hide canvas
$('#canvas').css({ visibility: 'hidden' });