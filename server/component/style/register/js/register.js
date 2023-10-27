$(document).ready(function () {
    initAnonymousRegister();
});

function initAnonymousRegister() {
    if ($('#anonymous-register')) {
        // check if the questions are not the same
        $('#anonymous-register').submit(function (event) {
            sec_q_1 = $('#security_question_1').val();
            sec_q_2 = $('#security_question_2').val();
            if (sec_q_1 == sec_q_2) {
                event.preventDefault(); // Prevent form submission
                $.alert({
                    title: 'Error!',
                    type: "red",
                    content: 'The security questions should be different!',
                });
            }
        });
    }
}