var editor = null;

$(document).ready(function () {
    loadJobConfig();
});


function createJSONEditor(schema) {
    if (editor) {
        editor.destroy();
    }
    editor = new JSONEditor($('#jobConfig')[0], {
        theme: 'bootstrap4',
        iconlib: 'fontawesome5',
        ajax: true,
        schema: schema,
        show_errors: "always",

    });
    editor.on('ready', () => {
        var crrValue = false;
        try {
            crrValue = JSON.parse($('#jobConfigValue').val());
        } catch (error) {
            // no value is set
        }
        if (crrValue) {
            editor.editors.root.setValue(crrValue, true)
        }
        check_condition_btns();
        editor.on('change', () => {
            $('#jobConfig').find('select').each(function () {
                if ($(this).prop('name').includes('job_schedule_types') || $(this).prop('name').includes('reminder_form_id')) {
                    // skip
                } else {
                    $(this).data('live-search', 'true'); // add live search
                    $(this).selectpicker();
                }
            });
            $('#jobConfigValue').val(JSON.stringify(editor.getValue()));
            check_condition_btns();
        });
        if ($('#jobConfig').hasClass('view-mode')) {
            // disable the form if we are in view mode
            editor.disable();
        }
    });
}

function check_condition_btns() {
    $('#jobConfig').find('[data-container="condition-btn"]').each(function () {
        var builder_field = $(this).parent().parent().parent().prev().find('[data-schemapath*="builder"]');
        var builder_val = getBuilderValues(builder_field);
        if (builder_val) {
            $(this).removeClass('btn-primary').addClass('btn-warning');
            $(this).text($(this).text().replace('Add', 'Edit'));
        } else {
            $(this).removeClass('btn-warning').addClass('btn-primary');
            $(this).text($(this).text().replace('Edit', 'Add'));
        }
    });
}

/**
 * Load the job config with json-editor library
 * @author Stefan Kodzhabashev
 * @date 2023-03-08
 * @returns {any}
 */
function loadJobConfig() {
    if ($('#jobConfig').length > 0) {
        var jobConfigSchema = $('#jobConfig').data('schema');
        createJSONEditor(jobConfigSchema);
        setDynamicEnums();
        $('#jobConfig').removeAttr('data-schema');
    }
}

function prepareEnumSource(values) {
    var enumValues = [];
    for (const key in values) {
        enumValues.push({
            value: key,
            text: values[key]
        });
    }
    var res = [];
    res.push({
        "source": enumValues,
        "title": "{{item.text}}",
        "value": "{{item.value}}"
    });
    return res;
}

function get_forms() {
    var formsArray = {};
    $('select[name="id_forms"]').find('option').each(function () {
        if (this.value) {
            formsArray[this.value] = this.text;
        }
    });
    return formsArray;
}

function get_time_intervals_text() {
    var obj = {};
    obj[1] = '1st';
    obj[2] = '2nd';
    obj[3] = '3rd';
    for (var i = 4; i <= 20; i++) {
        obj[i] = i + 'th';
    }
    return obj;
}

async function getAssets(filter) {
    var assets = [];
    jQuery.ajax({
        url: BASE_PATH + '/request/AjaxDataSource/get_assets',
        async: false,
        cache: false,
        type: 'post',
        data: { filter: filter },
        dataType: "json",
        success: function (data) {
            if (data.success) {
                try {
                    assets = JSON.parse(data.data);
                } catch (error) {
                    console.log('Error while parsing JSON', data.data);
                }
            }
            else {
                console.log(data);
            }
        }
    });
    return assets;
}

async function setDynamicEnums() {
    var groups = await getGroups();
    editor.schema.definitions.job_ref.properties.job_add_remove_groups.items.enum = groups;
    editor.schema.properties.selected_target_groups.items.enum = [...groups];
    var actionScheduleTypes = await getLookups('actionScheduleTypes');
    var weekdays = await getLookups('weekdays');
    var attachments = await getAssets('');
    editor.schema.definitions.schedule_time_ref.properties.job_schedule_types.enumSource = prepareEnumSource(actionScheduleTypes);
    editor.schema.definitions.schedule_time_ref.properties.send_on_day.enumSource = prepareEnumSource(weekdays);
    editor.schema.definitions.schedule_time_ref.properties.send_on.enumSource = prepareEnumSource(get_time_intervals_text());
    editor.schema.definitions.job_ref.properties.reminder_form_id.enumSource = prepareEnumSource(get_forms());
    editor.schema.definitions.notification_ref.properties.attachments.items.enum = attachments;
    createJSONEditor(editor.schema); // after changes the forms should be recreated
}

function setJsonValue(field, value) {
    var obj = editor.getValue();
    $(field).val(JSON.stringify(value, null, 3))
    var path = field.data('schemapath');
    var props = path.replace('root.', '').split('.');
    props.forEach(function (propName, index) {
        if (index === props.length - 1) {
            obj[propName] = value; // Assign new value to the final property
        } else {
            obj = obj[propName]; // Traverse the nested properties
        }
    });
    editor.setValue(editor.getValue());
}

function getBuilderValues(builder_field) {
    var obj = editor.getValue();
    var path = builder_field.data('schemapath');
    var props = path.replace('root.', '').split('.');
    var builder_field_value = '{}';
    props.forEach(function (propName, index) {
        if (obj.hasOwnProperty(propName)) {
            if (index === props.length - 1) {
                builder_field_value = obj[propName]
            } else {
                obj = obj[propName]; // Traverse the nested properties
            }
        }
    });
    if ($('.condition_builder').length > 0) {
        $('.condition_builder').queryBuilder('destroy');
    }
    var jqueryBuilderValue = null;
    try {
        if (Object.keys(builder_field_value).length !== 0) {
            jqueryBuilderValue = JSON.stringify(builder_field_value);
        }
    } catch (error) {

    }
    return jqueryBuilderValue;
}

JSONEditor.defaults.callbacks = {
    "button": {
        "showConditionBuilder": function (jseditor, e) {
            var builder_field = $(e.target).parent().parent().parent().prev().find('[data-schemapath*="builder"]');
            if (e.target.tagName === 'SPAN') {
                builder_field = $(e.target).parent().parent().parent().parent().prev().find('[data-schemapath*="builder"]');
            }
            var jsonLogic_field = $(builder_field).parent().prev().find('[data-schemapath*="jsonLogic"]');
            if (builder_field.length > 0) {
                prepareConditionBuilder(getBuilderValues(builder_field));
            }
            $(".condition_builder_modal_holder").modal({
                backdrop: false
            });
            $('.saveConditionBuilder').each(function () {
                $(this).attr('data-dismiss', 'modal');
                $(this).off('click').click(function () {
                    var rules = $('.condition_builder').queryBuilder('getRules');
                    setJsonValue(jsonLogic_field, rulesToJsonLogic(rules));
                    setJsonValue(builder_field, rules);
                })
            });
        }
    }
}
