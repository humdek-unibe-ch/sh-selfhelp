$(document).ready(function() {
    var $target = $('div.search-target');
    var $add_user = $('input[name="add_user"]');
    var $search = $('input[name="user_search"]');

    function select_item(id, email)
    {
        var ids = id.split('-');
        $add_user.val(ids[1]);
        $search.val(email);
        $target.empty();
    }

    $search.on('keydown', function(e) {
        if(e.key === "Escape")
        {
            $search.val("");
            $target.empty();
            $add_user.val("");
        }
        else if(e.key === "ArrowDown" || e.key === "ArrowUp")
        {
            e.preventDefault();
            var $active = $('.list-group-item-action.active[id|="user_search"]');
            var $next;
            if(e.key === "ArrowDown")
                $next = $active.next();
            else
                $next = $active.prev();
            if($next.hasClass('list-group-item-action'))
            {
                $active.removeClass('active');
                $next.addClass('active');
            }
        }
        else if(e.key === "Enter")
        {
            e.preventDefault();
            var $active = $('.list-group-item-action.active[id|="user_search"]');
            if($active.length !== 0)
                select_item($active.attr('id'), $active.text());
        }
    });
    $search.on('keyup', function(e) {
        var val = $(this).val();
        if(val.length < 1) return;
        if(e.key === "ArrowDown" || e.key === "ArrowUp"
            || e.key === "Enter" || e.key === "Escape")
            return;
        $.post(BASE_PATH + '/request/AjaxSearch/search_user_chat',
                {search: val}, function(data) {
            var $cont = $("<div/>", {class:"list-group"});
            if(data.success)
            {
                data.data.forEach(function(item, index) {
                    var active = "";
                    if(index === 0)
                    {
                        active = " active";
                        $add_user.val(parseInt(item.id));
                    }
                    $cont.append(
                        $("<div/>", {
                            class:"list-group-item list-group-item-action" + active,
                            id:"user_search-" + parseInt(item.id)
                        }).append(item.email)
                        .on('click', function() {
                            select_item($(this).attr('id'), $(this).text());
                        })
                    );
                });
                $target.html($cont);
            }
        }, 'json');
    });
});
