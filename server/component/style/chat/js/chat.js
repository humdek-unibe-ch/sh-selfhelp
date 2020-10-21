$(document).ready(function () {
    $('#subjects').DataTable({
        dom: 'ftipr',
        bInfo: false,
        bAutoWidth: false,
        aaSorting: []        
    });
    var $chat = $('.chatOverflow');
    var documenHeight = document.body.scrollHeight;
    var chatHeight = $chat.height();
    var newHeight = chatHeight - (documenHeight - $(window).height());
    if (newHeight > 0) {
        $chat.height(newHeight);
    }
    $chat.scrollTop(documenHeight);
    $(window).resize(function () {
        var newHeight = chatHeight - (documenHeight - $(window).height());
        if (newHeight > 0) {
            $chat.height(newHeight);
        }
        $chat.scrollTop(documenHeight);
    })
});
