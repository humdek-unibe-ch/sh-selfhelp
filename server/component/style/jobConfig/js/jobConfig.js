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
        crrValue = false;
        try {
            crrValue = JSON.parse($('#jobConfigValue').val());
        } catch (error) {
            // no value is set
        }
        if (crrValue) {
            editor.editors.root.setValue(crrValue, true)
        }
        editor.on('change', () => {
            $('#jobConfig').find('select').each(function () {
                $(this).selectpicker();
                $(this).selectpicker('refresh');
            });
            console.log(editor.getValue());
            $('#jobConfigValue').val(JSON.stringify(editor.getValue()));
        });
        if ($('#jobConfig').hasClass('view-select')) {
            // disable the form if we are in view mode
            editor.disable();
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
        var schemaUrl = window.location.protocol + "//" + window.location.host + BASE_PATH + "/schemas/jobConfig/jobConfig.json";
        // get the schema with AJAX call
        $.ajax({
            dataType: "json",
            url: schemaUrl,
            success: (retrievedSchema) => {
                createJSONEditor(retrievedSchema);
                setDynamicEnums();
            }
        });
    }
}

function prepareEnumSource(values) {
    enumValues = [];
    for (const key in values) {
        console.log(key, values[key]);
        enumValues.push({
            value: key,
            text: values[key]
        });
    }
    res = [];
    res.push({
        "source": enumValues,
        "title": "{{item.text}}",
        "value": "{{item.value}}"
    });
    return res;
}

async function setDynamicEnums() {
    var groups = await getGroups();
    editor.schema.definitions.job_ref.properties.job_add_remove_groups.items.enum = groups;
    console.log(editor.schema.properties.selected_target_groups.items.enum);
    editor.schema.properties.selected_target_groups.items.enum = [...groups];
    console.log(JSON.stringify(editor.schema.properties.selected_target_groups.items.enum));
    var actionScheduleTypes = await getLookups('actionScheduleTypes');
    editor.schema.definitions.schedule_time_ref.properties.job_schedule_types.enumSource = prepareEnumSource(actionScheduleTypes);
    createJSONEditor(editor.schema); // after changes the forms should be recreated
}
