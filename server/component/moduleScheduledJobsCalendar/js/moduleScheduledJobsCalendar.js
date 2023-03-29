/**
 * The calendar object for fullcalendar.js.
 * @type {object}
 */
var calendar;

/**
 * Initialize the fullcalendar.js calendar on document ready.
 * @function
 * @returns {void}
 */
$(document).ready(function () {
    init_context_menu();
    init_view_button()
    init_calendar();
});

/**
 * Initialize the view button to allow the user to view the calendar for a specific user.
 * @function
 * @returns {void}
 */
function init_view_button() {
    $('#scheduled-jobs-view-calendar-btn').click(function (event) {
        event.preventDefault();
        var view_url = $(this).attr('href');
        var selected_user = $('#scheduled-jobs-calendar-selected-user').val();
        if (selected_user) {
            view_url = view_url.replace(':uid', selected_user);
            window.location.href = view_url;
        } else {
            $.alert({
                title: 'View calendar',
                content: 'Please select a user',
            });
        }
    })
}

/**
 * Prepare the scheduled events for display in the calendar.
 * @function
 * @param {Array} scheduled_events - The scheduled events to be displayed in the calendar.
 * @returns {Array} - The formatted scheduled events.
 */
function prepare_scheduled_events(scheduled_events) {
    scheduled_events.forEach(job => {
        job.url = job.url.replace(':sjid', job.id);
        job['classNames'] = 'scheduled-jobs-calendar-event scheduled-jobs-calendar-' + job.status_code + ' scheduled-jobs-calendar-' + job.type_code;
        if (job.status_code == 'done') {
            job['backgroundColor'] = "green";
        } else if (job.status_code == 'failed') {
            job['backgroundColor'] = "red";
        };
    });
    return scheduled_events;
}

/**
 * Initialize the fullcalendar.js calendar.
 * @function
 * @returns {void}
 */
function init_calendar() {
    var scheduled_events = $("#scheduled-jobs-events").data('scheduled-jobs');
    calendar = new FullCalendar.Calendar($('.scheduled-jobs-calendar-view')[0], {
        initialView: 'dayGridMonth',
        themeSystem: 'bootstrap',
        headerToolbar: {
            left: 'prev,next,today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek,dayGridDay,listWeek'
        },
        weekNumbers: true,
        weekNumberFormat: {
            week: 'long'
        },
        titleFormat: {
            // day: 'MM/dd'
            week: "long"
        },
        height: 'auto',
        firstDay: 1,
        eventTimeFormat: { hour: 'numeric', minute: '2-digit', hour12: false },
        events: prepare_scheduled_events(scheduled_events),
        eventContent: function (info) {
            var dot = document.createElement('div');
            $(dot).addClass('fc-daygrid-event-dot');
            var time = document.createElement('div');
            $(time).addClass('fc-event-time');
            time.innerHTML = info.event.start.toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric', hour12: false }) + " <span>[" + info.event.extendedProps.type_code + "]</span>";
            var title = document.createElement('div');
            $(title).addClass('fc-event-title');
            title.innerHTML = info.event.title;
            return { domNodes: [dot, time, title] }
        },
        eventDidMount: (info) => {
            info.el.className = info.el.className + " context-menu-event";
            $(info.el).attr("data-event", JSON.stringify(info.event));
        }
    });
    calendar.render();
}

/**
* Initialize the context menu for events.
* @function
*/
function init_context_menu() {
    $(function () {
        $.contextMenu({
            selector: '.context-menu-event',
            items: {
                view: {
                    name: "View job",
                    icon: "fas fa-fw fa-eye",
                    className: "",
                    isHtmlName: true,
                    callback: function (itemKey, opt, e) {
                        var event = $(opt.$trigger[0]).data('event');
                        window.open(event.url, '_blank');
                    }
                },
                separator: "----------------------",
                execute: {
                    name: "Execute job",
                    icon: "fas fa-fw fa-play text-danger",
                    className: "text-danger",
                    callback: function (itemKey, opt, e) {
                        $.confirm({
                            title: 'Execute Job!',
                            content: 'Are you sure that you want to execute this job right now?',
                            buttons: {
                                confirm: function () {
                                    var event = $(opt.$trigger[0]).data('event');
                                    $.post(event.url, { mode: 'execute' }, function () {
                                        $.get(window.location.href, function (result) {
                                            rerenderEvents(result);
                                        });
                                    });
                                },
                                cancel: function () {

                                }
                            }
                        });
                    }
                },
                delete: {
                    name: "Delete job",
                    icon: "fas fa-fw fa-trash text-danger",
                    className: "text-danger",
                    callback: function (itemKey, opt, e) {
                        $.confirm({
                            title: 'Delete Scheduled Jobs!',
                            content: 'Are you sure that you want to delete this job?',
                            buttons: {
                                confirm: function () {
                                    var event = $(opt.$trigger[0]).data('event');
                                    $.post(event.url, { mode: 'delete' }, function () {
                                        $.get(window.location.href, function (result) {
                                            rerenderEvents(result);
                                        });
                                    });
                                },
                                cancel: function () {

                                }
                            }
                        });
                    }
                }
            }
        });
    });
}

/**
* Re-render the events in the calendar.
* @function
* @param {any} result - The result of the AJAX request.
* @returns {void}
*/
function rerenderEvents(result) {
    var scheduledEvents = $(result).filter('#scheduled-jobs-events').data('scheduled-jobs');
    calendar.removeAllEventSources();
    calendar.addEventSource(prepare_scheduled_events(scheduledEvents));
    calendar.refetchEvents();
}