$(document).ready(function() {
    initAutocomplete();
});

function initAutocomplete(){
    var prefix = "autocomplete";
    $('div.input-autocomplete').each(function() {
        var $target = $(this).children('div.input-autocomplete-search-target');
        var $debug = $(this).children('div.input-autocomplete-debug');
        var $callback = $(this).children('div.input-autocomplete-callback');
        var $value = $(this).find('input.input-autocomplete-value');
        var $search = $(this).find('input.input-autocomplete-search');

        function select_item(id, email)
        {
            $value.val(id.substr(prefix.length + 1));
            $search.val(email);
            $target.empty();
        }

        $search.on('keydown', function(e) {
            if(e.key === "Escape")
            {
                $search.val("");
                $target.empty();
                $value.val("");
            }
            else if(e.key === "ArrowDown" || e.key === "ArrowUp")
            {
                e.preventDefault();
                var $active = $(`.list-group-item-action.active[id|="${prefix}"]`);
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
                var $active = $(`.list-group-item-action.active[id|="${prefix}"]`);
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
            $.post(BASE_PATH + '/request/' + $callback.text(),
                    {search: val}, function(data) {
                var $cont = $("<div/>", {class:"list-group"});
                if(data.success)
                {
                    data.data.forEach(function(item, index) {
                        var active = "";
                        if(index === 0)
                        {
                            active = " active";
                            $value.val(item.id);
                        }
                        $cont.append(
                            $("<div/>", {
                                class: "list-group-item list-group-item-action" + active,
                                id: prefix + '-' + item.id
                            }).append(item.value)
                            .on('click', function() {
                                select_item($(this).attr('id'), $(this).text());
                            })
                        );
                    });
                    $target.html($cont);
                }
                else {
                    console.log(data);
                    $debug.html(data.data);
                }
            }, 'json');
        });
    });
}
