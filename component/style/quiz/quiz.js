$(document).ready(function() {
    $('button[id|=quizBtn-right]').click(function() {
        var id = $(this).attr('id').split('-');
        $('div#quizContent-right-' + id[2]).slideToggle("fast");
        $('div#quizContent-wrong-' + id[2]).hide();
    });
    $('button[id|=quizBtn-wrong]').click(function() {
        var id = $(this).attr('id').split('-');
        $('div#quizContent-wrong-' + id[2]).slideToggle("fast");
        $('div#quizContent-right-' + id[2]).hide();
    });
});
