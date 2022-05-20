function isHidden(el) {
    return (el.offsetParent === null)
}

function setRequiredIfDisplayed(elements) {
    for (let i = 0; i < elements.length; i++) {
        const el = elements[i];
        $(el).attr('required', !isHidden(el));
    }
}

function setRequired(elements, val) {
    for (let i = 0; i < elements.length; i++) {
        const el = elements[i];
        $(el).attr('required', val);
    }
}

function adjustRequiredFields() {
    setRequiredIfDisplayed($('select[name="schedule_info[id_formActionScheduleTypes]"]'));
    setRequiredIfDisplayed($('input[name="schedule_info[custom_time]"]'));
    if ($('select[name="schedule_info[qualtricScheduleTypes]"] option:selected').text().includes('time period on a weekday')) {
        // reuqired only for weekdays
        setRequiredIfDisplayed($('input[name="schedule_info[send_on_day_at]"]'));
    } else {
        setRequired($('input[name="schedule_info[send_on_day_at]"]'), false);
    }
    setRequiredIfDisplayed($('select[name="id_forms_reminder"]'));
    setRequiredIfDisplayed($('select[name="schedule_info[notificationTypes]"]'));
    setRequiredIfDisplayed($('select[name="schedule_info[qualtricScheduleTypes]"]'));
    setRequiredIfDisplayed($('select[name="schedule_info[send_on_day]"]'));
    setRequiredIfDisplayed($('select[name="schedule_info[send_on]"]'));
    setRequiredIfDisplayed($('input[name="schedule_info[send_after]"]'));
    setRequiredIfDisplayed($('select[name="schedule_info[send_after_type]"]'));
    setRequiredIfDisplayed($('input[name="schedule_info[recipient]"]'));
    setRequiredIfDisplayed($('input[name="schedule_info[subject]"]'));
    setRequiredIfDisplayed($('input[name="schedule_info[from_email]"]'));
    setRequiredIfDisplayed($('input[name="schedule_info[from_name]"]'));
    setRequiredIfDisplayed($('input[name="schedule_info[reply_to]"]'));
    setRequiredIfDisplayed($('select[name="schedule_info[linked_action]"]'));
}

function adjustActionScheduleType() {
    $('.style-section-from_email').removeClass('d-none');
    $('.style-section-from_name').removeClass('d-none');
    $('.style-section-reply_to').removeClass('d-none');
    $('.style-section-to').removeClass('d-none');
    $('.style-section-subject').removeClass('d-none');
    $('.style-section-body').removeClass('d-none');
    $('.style-section-type').removeClass('d-none');
    $('#section-schedule_info').addClass('d-none');
    $('.style-section-id_forms_reminder').addClass('d-none');
    $('.style-section-id_formActions').addClass('d-none');
    $('.style-section-valid').addClass('d-none');
    $('.style-section-linked_action').addClass('d-none');
    $('.style-section-targetGroups').addClass('d-none');
    if ($('select[name="id_formActionScheduleTypes"] option:selected').text().includes('Notification') ||
        $('select[name="id_formActionScheduleTypes"] option:selected').text().includes('Task') ||
        $('select[name="id_formActionScheduleTypes"] option:selected').text().includes('Reminder')) {
        $('#section-schedule_info').removeClass('d-none');
    }
    if ($('select[name="id_formActionScheduleTypes"] option:selected').text().includes('Reminder')) {
        $('.style-section-id_forms_reminder').removeClass('d-none');
        $('.style-section-id_formActions').removeClass('d-none');
        if ($('select[name="schedule_info[qualtricScheduleTypes]"] option:selected').text().includes('time period on a weekday')) {
            $('.style-section-linked_action').removeClass('d-none');
        }
    }
    if ($('select[name="id_formActionScheduleTypes"] option:selected').text().includes('Task')) {
        $('.style-section-from_email').addClass('d-none');
        $('.style-section-from_name').addClass('d-none');
        $('.style-section-reply_to').addClass('d-none');
        $('.style-section-to').addClass('d-none');
        $('.style-section-subject').addClass('d-none');
        $('.style-section-body').addClass('d-none');
        $('.style-section-type').addClass('d-none');
        $('.style-section-targetGroups').removeClass('d-none');
    }
    adjustRequiredFields();
}

function adjustScheduleType() {
    $('#custom_time_holder').addClass('d-none');
    $('.send_after').addClass('d-none');
    $('.style-section-send_after_type').addClass('d-none');
    $('.style-section-send_on').addClass('d-none');
    $('.style-section-send_on_day').addClass('d-none');
    $('.style-section-linked_action').addClass('d-none');
    $('#at_time_holder').addClass('d-none');
    if ($('select[name="schedule_info[qualtricScheduleTypes]"] option:selected').text().includes('fixed datetime')) {
        $('#custom_time_holder').removeClass('d-none');
    } else if ($('select[name="schedule_info[qualtricScheduleTypes]"] option:selected').text().includes('time period on a weekday')) {
        $('.style-section-send_on').removeClass('d-none');
        $('.style-section-send_on_day').removeClass('d-none');
        $('#at_time_holder').removeClass('d-none');
        if ($('select[name="id_formActionScheduleTypes"] option:selected').text().includes('Reminder')) {
            $('.style-section-linked_action').removeClass('d-none');
        }
    } else if ($('select[name="schedule_info[qualtricScheduleTypes]"] option:selected').text().includes('time period')) {
        $('.send_after').removeClass('d-none');
        $('.style-section-send_after_type').removeClass('d-none');
        $('#at_time_holder').removeClass('d-none');
    }
    adjustRequiredFields();
}

function adjustNotificationTypes() {
    if ($('select[name="schedule_info[notificationTypes]"] option:selected').text().includes('Push Notification')) {
        $('.style-section-from_email').addClass('d-none');
        $('.style-section-from_name').addClass('d-none');
        $('.style-section-reply_to').addClass('d-none');
        $('.style-section-url').removeClass('d-none');
    } else if ($('select[name="schedule_info[notificationTypes]"] option:selected').text().includes('Email')) {
        $('.style-section-from_email').removeClass('d-none');
        $('.style-section-from_name').removeClass('d-none');
        $('.style-section-reply_to').removeClass('d-none');
        $('.style-section-url').addClass('d-none');
    }
    adjustRequiredFields();
}

$(document).ready(function () {
    adjustScheduleType();
    adjustActionScheduleType();
    adjustNotificationTypes();
    adjustRequiredFields();
    $('select').selectpicker();
    if ($('textarea[name="schedule_info[body]"]')[0]) {
        var simplemde = new EasyMDE({
            autoDownloadFontAwesome: false,
            spellChecker: false,
            renderingConfig: {
                singleLineBreaks: false
            }
        });
        if ($('.style-section-body code').length > 0) {
            $('.style-section-body code').first().html(simplemde.options.previewRender($('.style-section-body code').first().html()));
        }
    }

    // datepicker ***********************************************************************************
    $('#custom_time').flatpickr({
        enableTime: true,
        dateFormat: 'd-m-Y H:i',
        time_24hr: true,
        weekNumbers: true,
        minDate: "today",
        allowInput: true
    });


    $('#btncustom_time').on("click", function (e) {
        $('#custom_time').focus();
    })

    // at time ***********************************************************************************
    $('#send_on_day_at').flatpickr({
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        allowInput: true
    });


    $('#btnsend_on_day_at').on("click", function (e) {
        $('#send_on_day_at').focus();
    })

    $('#clearBtnsend_on_day_at').on("click", function (e) {
        if (!$('#send_on_day_at').attr('disabled')) {
            $('#send_on_day_at').val('');
        }
    })

    $('#section-composeEmailForm .btn-warning').first().on('click', function (e) {
        if (new Date() >= flatpickr.parseDate($('#custom_time').val(), 'd-m-Y H:i')) {
            e.stopPropagation();
            e.preventDefault();
            $.alert({
                title: 'Wrong date!',
                content: 'The selected time already passed!',
            });
        }
        if (!flatpickr.parseDate($('#custom_time').val())) {
            e.stopPropagation();
            e.preventDefault();
            $.alert({
                title: 'Missing date!',
                content: 'Please enter date',
            });
        }
    });

    //on action_schedule_type change ******************************************************************************
    $('select[name="id_formActionScheduleTypes"]').on('change', function () {
        adjustActionScheduleType();
    });

    //on when (schedule_type) change ************************************************************************************
    $('select[name="schedule_info[qualtricScheduleTypes]"]').on('change', function () {
        adjustScheduleType();
    });

    //on notificationTypes change ******************************************************************************
    $('select[name="schedule_info[notificationTypes]"]').on('change', function () {
        adjustNotificationTypes();
    });



});