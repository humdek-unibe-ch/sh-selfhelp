$(document).ready(function () {
    initForm();
});

function initForm() {
    $("form").each(function () {
        var confirmation = $(this).data('confirmation');
        if (confirmation && confirmation['confirmation_title']) {
            $(this).submit((e) => {
                e.preventDefault();
                var form = this;
                $.confirm({
                    type: "red",
                    title: confirmation['confirmation_title'],
                    content: confirmation['confirmation_message'],
                    buttons: {
                        confirm:
                        {
                            text: confirmation['confirmation_continue'],
                            action: function () {
                                form.submit();
                            }
                        },
                        cancel: {
                            text: confirmation['confirmation_cancel'],
                            action: function () { }
                        }
                    }
                });
            })
        }
    });
}