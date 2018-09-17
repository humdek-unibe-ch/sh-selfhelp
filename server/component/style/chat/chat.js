$(document).ready(function() {
    var $chat = $('.chat');
    var documenHeight = document.body.scrollHeight;
    var chatHeight = $chat.height();
    $chat.height(chatHeight - (documenHeight - $(window).height()));
    $chat.scrollTop(documenHeight);
    $(window).resize(function() {
        $chat.height(chatHeight - (documenHeight - $(this).height()));
        $chat.scrollTop(documenHeight);
    })
});
