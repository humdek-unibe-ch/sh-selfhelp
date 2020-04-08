// $(document).ready(() => {
//     $('div.filter-toggle').each(function(idx, $filter) {
//         let $filter_data = $filter.children('div.filter-toggle-data');
//         let $filter_switch = $filter.children('button.filter-toggle-switch');
//         let $spinner = $filter_switch.children('i.filter-toggle-pending');
//         let filter_data = parseFilterData($filter_data);
//         let event = new Event("data-filter-" + filter_data.data_source);
//         $filter_switch.click(function() {
//             $spinner.removeClass('d-none');
//             $.post(
//                 BASE_PATH + '/request/AjaxDataSource/set_data_filter',
//                 {
//                     action: $(this).hasClass('active') ? "rm" : "add",
//                     name: filter_data.name,
//                     value: filter_data.value,
//                     data_source: filter_data.data_source
//                 },
//                 function(data) {
//                     if(data.success)
//                     {
//                         console.log("dispatch event: " + event.type);
//                         window.dispatchEvent(event);
//                         $spinner.addClass('d-none');
//                         $filter_switch.toggleClass('active');
//                     }
//                     else {
//                         console.log(data);
//                     }
//                 },
//                 'json'
//             );
//         });
//     });
// });


function filterActionHandler($filter, filter_data, event) {
    $filter.find('button.filter-toggle-switch').each(function(idx) {
        let $switch = $(this);
        let $spinner = $switch.children('i.filter-toggle-pending');
        $switch.click(function() {
            filterEventHandler($spinner, $switch, idx, filter_data, event);
        });
    });
}
