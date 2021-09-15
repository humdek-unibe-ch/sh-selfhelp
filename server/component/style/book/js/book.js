$(document).ready(function () {
    $('.book-turnjs').each(function () {
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
                // when page is turned add the page number to the url
                window.location.hash = "#page-" + page;
                var book = $(this),
                    currentPage = book.turn('page'),
                    pages = book.turn('pages');
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
                    var currentView = book.turn('view');
                    currentView.forEach(p => {
                        $(this).find('[page="' + p + '"]').find(':submit').click();
                    });
                }
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

            }
        }
        $(this).turn(config);
        // check if there is page number in the url
        var pageNumber = window.location.hash.substring(1);
        if (pageNumber) {
            $(this).turn('page', pageNumber.replace('page-', ''));
        }
        if (slider) {
            $('#slider').slider('option', 'max', $(this).turn('pages') / 2 + 1);
        }
        $(this).addClass('animated');
        // Show canvas
        $('#canvas').css({ visibility: '' });

    });

    setKeyboardKeys();
});

function setKeyboardKeys() {
    $(document).keydown(function (e) {
        var previous = 37, next = 39;
        switch (e.keyCode) {
            case previous:
                $('.book-turnjs').turn('previous');
                break;
            case next:
                $('.book-turnjs').turn('next');
                break;
        }

    });
}

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