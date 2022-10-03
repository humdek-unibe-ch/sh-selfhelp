$(document).ready(function () {
    initButton();
});

function initButton() {
    $('.btn').each(function () {
        var confirmation = $(this).data('confirmation');
        if (confirmation && confirmation['confirmation_title']) {
            $(this).off('click').click((e) => {
                e.preventDefault();
                var btn = this;
                $.confirm({
                    title: confirmation['confirmation_title'],
                    content: confirmation['label_message'],
                    buttons: {
                        confirm:
                        {
                            text: confirmation['label_continue'],
                            action: function () {
                                window.location = $(btn).attr('href');
                            }
                        },
                        cancel: {
                            text: confirmation['label_cancel'],
                            action: function () { }
                        }
                    }
                });
            })
        }
    });
}