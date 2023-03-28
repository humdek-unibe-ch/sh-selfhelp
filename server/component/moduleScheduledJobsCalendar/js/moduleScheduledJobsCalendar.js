$(document).ready(function () {
    let ec = new EventCalendar($('.scheduled-jobs-calendar-view')[0], {
        view: 'dayGridMonth',
        headerToolbar: {
            start: 'prev,next today',
            center: 'title',
            end: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek resourceTimeGridWeek'
        },
        events: [
            // your list of events
        ]
    });
});