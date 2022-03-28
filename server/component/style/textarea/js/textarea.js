// jsonLogic export
const jsonLogicOperators = {
    field_equal: { op: '==' },
    field_not_equal: { op: '!=' },
    field_greater: { op: '>' },
    field_less: { op: '<' },
    equal: { op: '==' },
    not_equal: { op: '!=' },
    greater: { op: '>' },
    less: { op: '<' },
    less_or_equal: { op: '<=' },
    greater_or_equal: { op: '>=' },
    not_in: { op: '!=' },
    in: { op: '==' },
    in_one_of: { op: '==' },
};

var dataConfigInitCalls = {};

$(document).ready(function () {
    autosize($('textarea'));
    check_textarea_locked_after_submit();
    $('.json').each(function () {
        // load the monaco editor for json fields
        require.config({ paths: { vs: BASE_PATH + '/js/ext/vs' } });
        var json = $(this)[0];
        if ($(json).prev().attr('name').includes('jquery_builder_json')) {
            // this field is hidden and a holder only
            $(json).parent().parent().addClass('d-none'); //hide the label
            return;
        } else {
            if ($(json).prev().attr('name').includes('condition')) {
                $(json).addClass('d-none');
            }
            require(['vs/editor/editor.main'], function () {
                var model = null;
                if ($(json).prev().attr('name').includes('data_config')) {
                    model = setDataConfigSchema(monaco, json);
                } else if ($(json).prev().attr('name').includes('condition')) {
                    model = setConditionSchema(monaco, json);
                } else if ($(json).prev().parent().attr('class') && $(json).prev().parent().attr('class').includes('qualtricsSurveyConfig')) {
                    model = setQualtricsSurveyConfigSchema(monaco, json);
                }
                var editorOptions = {
                    value: $(json).prev().val(),
                    language: 'json',
                    automaticLayout: true,
                    renderLineHighlight: "none"
                }
                if (model) {
                    editorOptions['model'] = model;
                }
                var editor = monaco.editor.create(json, editorOptions);
                editor.getAction('editor.action.formatDocument').run().then(() => {
                    calcMonacoEditorSize(editor, json);
                });
                editor.onDidChangeModelContent(function (e) {
                    $(json).prev().val(editor.getValue());
                    calcMonacoEditorSize(editor, json);
                });
                if ($(json).prev().attr('name').includes('data_config')) {
                    showDataConfiBuilder(json, editor);
                } else if ($(json).prev().attr('name').includes('condition')) {
                    var jquerBuilderJsonInput;
                    $('textarea').each(function () {
                        if ($(this).attr('name') && $(this).attr('name').includes('jquery_builder_json')) {
                            jquerBuilderJsonInput = this;
                        }
                    })
                    showConditionBuilder(editor, jquerBuilderJsonInput);
                } else if ($(json).prev().parent().attr('class') && $(json).prev().parent().attr('class').includes('qualtricsSurveyConfig')) {
                    showQualtricsSurveyConfiBuilder(json, editor);
                } else if ($(json).prev().parent().attr('class') && $(json).prev().parent().attr('class').includes('actionConfig')) {
                    showActionConfiBuilder(json, editor);
                    var jquerBuilderJsonInput;
                    $('textarea').each(function () {
                        if ($(this).attr('class') && $(this).attr('class').includes('action_condition_builder')) {
                            jquerBuilderJsonInput = this;
                        }
                    })
                    showConditionBuilder(editor, jquerBuilderJsonInput);
                    showActionConditionBuilder(editor, jquerBuilderJsonInput);
                }
            });
        }
    })
});

function calcMonacoEditorSize(editor, object) {
    // calculate the size of the editor based on the code
    // we keep max size 500px
    var contentHeight = editor.getModel().getLineCount() * 19;
    if (contentHeight < 100) {
        contentHeight = 100;
    }
    if (contentHeight > 500) {
        contentHeight = 500;
    }
    $(object).height(contentHeight);
    editor.layout();
}

function setDataConfigSchema(monaco, json) {
    // get the dataConfig scheme
    var schema = window.location.protocol + "//" + window.location.host + BASE_PATH + "/schemas/dataConfig/dataConfig.json";
    var modelUri = monaco.Uri.parse(schema); // a made up unique URI for our model
    var model = monaco.editor.createModel($(json).prev().val(), "json", modelUri);

    // configure the JSON language support with schemas and schema associations
    monaco.languages.json.jsonDefaults.setDiagnosticsOptions({
        validate: true,
        enableSchemaRequest: true,
        schemas: [{
            uri: "http://selfhelp/dataConfig.json", // id of the first schema
            fileMatch: [modelUri.toString()], // associate with our model
            schema: {
                "$schema": schema,
                "$id": schema,
                "title": "Data config Schema",
                "description": "Data config JSON schema",
                "$ref": schema
            }
        }]
    });
    return model;
}

function setConditionSchema(monaco, json) {
    // get the json ligic schemes
    var schema = window.location.protocol + "//" + window.location.host + BASE_PATH + "/schemas/json-logic/json-logic.json";
    var modelUri = monaco.Uri.parse(schema); // a made up unique URI for our model
    var model = monaco.editor.createModel($(json).prev().val(), "json", modelUri);

    // configure the JSON language support with schemas and schema associations
    let r = monaco.languages.json.jsonDefaults.setDiagnosticsOptions({
        validate: true,
        enableSchemaRequest: true,
        schemas: [{
            uri: "http://selfhelp/json-logic.json", // id of the first schema
            fileMatch: [modelUri.toString()], // associate with our model
            schema: {
                "$schema": "http://json-schema.org/draft-07/schema#",
                "$id": schema,
                "title": "JSON-Logic Schema",
                "description": "Build complex rules, serialize them as JSON, share them between front-end and back-end.",
                "$ref": schema
            }
        }]
    });
    return model;
}

function setQualtricsSurveyConfigSchema(monaco, json) {
    // get the qualtricsSurveyConfig schemes
    var schema = window.location.protocol + "//" + window.location.host + BASE_PATH + "/schemas/qualtricsSurveyConfig/qualtricsSurveyConfig.json";
    var modelUri = monaco.Uri.parse(schema); // a made up unique URI for our model
    var model = monaco.editor.createModel($(json).prev().val(), "json", modelUri);

    // configure the JSON language support with schemas and schema associations
    let r = monaco.languages.json.jsonDefaults.setDiagnosticsOptions({
        validate: true,
        enableSchemaRequest: true,
        schemas: [{
            uri: "http://selfhelp/qualtricsSurveyConfig.json", // id of the first schema
            fileMatch: [modelUri.toString()], // associate with our model
            schema: {
                "$schema": "http://json-schema.org/draft-07/schema#",
                "$id": schema,
                "title": "Qualtrics Survey Config Schema",
                "description": "Qualtrics Survey Config Schema",
                "$ref": schema
            }
        }]
    });
    return model;
}

function check_textarea_locked_after_submit() {
    // check if the text are shoud be locked after submit
    $('.selfhelpTextArea').each(function () {
        if ($(this).data('locked_after_submit') && $(this).val()) {
            $(this).prop('readonly', true);
        }
    })
}

//get the json as object from the json element
function getJson(json) {
    try {
        return JSON.parse($(json).prev().val());
    } catch (error) {
        return null;
    }
}


// ********************************************* DATA CONFIG BUILDER *****************************************

// show the data config builder
// on click the modal is loaded and show the builder
// on change it updates the monaco editor and the monaco editor updates the input fields
function showDataConfiBuilder(json, monacoEditor) {
    var editor;
    var defValue = getJson(json);
    $('.dataConfigBuilderBtn').each(function () {
        $(this).click(() => {
            $(".data_config_builder_modal_holder").modal({
                backdrop: false
            });
            if (editor) {
                // set the latest value if the user changed the JSON manually                
                editor.setValue(getJson(json));
            }
            $('.data_config_builder_modal_holder').on('hidden.bs.modal', function (e) {

            })
            $('.saveDataConfig').each(function () {
                $(this).attr('data-dismiss', 'modal');
                // on modal close set the value to the Monaco editor
                $(this).click(function () {
                    monacoEditor.getModel().setValue(JSON.stringify(editor.getValue(), null, 3));
                })
            });
        });
    });
    var schemaUrl = window.location.protocol + "//" + window.location.host + BASE_PATH + "/schemas/dataConfig/dataConfig.json";
    // get the schema with AJAX call
    $.ajax({
        dataType: "json",
        url: schemaUrl,
        success: (s) => {
            editor = new JSONEditor($('.data_config_builder')[0], {
                theme: 'bootstrap4',
                iconlib: 'fontawesome5',
                ajax: true,
                schema: s,
                startval: defValue,
                show_errors: "always",
                display_required_only: true
            });

            // check for new data soure or new fields additions
            editor.on("change", function () {
                for (let key in editor.editors) {
                    if (editor.editors.hasOwnProperty(key) && key !== 'root' && editor.watchlist && !editor.watchlist[key] &&
                        (key.includes('table') || key.includes('type') || key.includes('field_name'))) {
                        if (key.includes('type')) {
                            // populate tables for new data source
                            getTableNames(editor.getEditor(key).getValue(), editor, key.replace('type', 'table'));
                        } else if (key.includes('field_name')) {
                            // populate field names for the new field
                            var keys = key.split('.');
                            var dataSourceKey = keys[0] + '.' + keys[1];
                            getTableFieldNames(
                                this.getEditor(dataSourceKey + '.type').getValue(),
                                this.getEditor(dataSourceKey + '.table').getValue(),
                                this,
                                key,
                                true
                            );
                        }
                        editor.watch(key, dataConfigWatcherCallback.bind(editor, key)); // add the keys again - it is used to add watches for new sources
                    }
                }
            })

            // Initialization
            for (let key in editor.editors) {
                if (editor.editors.hasOwnProperty(key) && key !== 'root') {
                    if (key.includes('type')) {
                        // populate tables
                        var k = key.replace('type', 'table');
                        dataConfigInitCalls[k] = false;
                        getTableNames(editor.getEditor(key).getValue(), editor, key.replace('type', 'table'), editor);
                    } else if (key.includes('table') && editor.getEditor(key) && editor.getEditor(key).getValue()) {
                        // populate fields
                        var k = key.replace('type', 'table');
                        dataConfigInitCalls[k] = false;
                        getTableFieldNames(
                            editor.getEditor(key.replace('table', 'type')).getValue(),
                            editor.getEditor(key).getValue(),
                            editor,
                            key.replace('table', 'fields'),
                            false,
                            editor
                        );
                    }
                }
            }
        }
    });
}

const dataConfigWatcherCallback = function (path) {
    if (path.includes('type') && this.getEditor(path) && this.getEditor(path).getValue()) {
        getTableNames(this.getEditor(path).getValue(), this, path.replace('type', 'table'));
    } else if (path.includes('table') && this.getEditor(path) && this.getEditor(path).getValue()) {
        getTableFieldNames(
            this.getEditor(path.replace('table', 'type')).getValue(),
            this.getEditor(path).getValue(),
            this,
            path.replace('table', 'fields'),
            false
        );
    }
}

function checkAllDataConfigInitCalls(editor) {
    let result = true;
    for (const key in dataConfigInitCalls) {
        result = dataConfigInitCalls[key] && result;
    }
    if (result) {
        // all calls are done
        for (let key in editor.editors) {
            if (editor.editors.hasOwnProperty(key) && key !== 'root' &&
                (key.includes('table') || key.includes('type') || key.includes('field_name'))) {
                editor.watch(key, dataConfigWatcherCallback.bind(editor, key)); // add watch events to all exisitng keys containing [table, type, field_name]
            }
        }
    }
}

// AJAX call to get the table names
function getTableNames(type, obj, path, editor) {
    $.post(
        BASE_PATH + '/request/AjaxDataSource/get_table_names',
        { type: type },
        function (data) {
            if (data.success) {
                var tableNames = [];
                try {
                    tableNames = JSON.parse(data.data);
                } catch (error) {
                    console.log("Error while parsing", data.data);
                }
                var fc = obj.getEditor(path).parent;
                fc.original_schema.properties.table["enum"] = tableNames;
                fc.schema.properties.table["enum"] = tableNames;
                fc.removeObjectProperty('table');
                //delete the cache
                delete fc.cached_editors.table;
                fc.addObjectProperty('table');
            }
            else {
                console.log(data);
            }
            if (editor) {
                dataConfigInitCalls[path] = true;
                checkAllDataConfigInitCalls(editor);
            }
        },
        'json'
    );
}

// AJAX call to get the table fields
function getTableFieldNames(type, formName, obj, path, init, editor) {
    $.post(
        BASE_PATH + '/request/AjaxDataSource/get_table_fields',
        {
            type: type,
            name: formName
        },
        function (data) {
            if (data.success) {
                var fieldNames = [];
                try {
                    fieldNames = JSON.parse(data.data);
                } catch (error) {
                    console.log("Error while parsing", data.data);
                }
                if (init) {
                    var fc = obj.getEditor(path).parent;
                    fc.original_schema.properties.field_name["enum"] = fieldNames;
                    fc.schema.properties.field_name["enum"] = fieldNames;
                    fc.removeObjectProperty('field_name');
                    //delete the cache
                    delete fc.cached_editors.field_name;
                    fc.addObjectProperty('field_name');
                } else {
                    for (let i = 0; i < obj.getEditor(path).rows.length; i++) {
                        var currValue = obj.getEditor(path + '.' + i + '.field_name').getValue().valueOf();
                        var fc = obj.getEditor(path + '.' + i + '.field_name').parent;
                        fc.original_schema.properties.field_name["enum"] = fieldNames;
                        fc.schema.properties.field_name["enum"] = fieldNames;
                        fc.removeObjectProperty('field_name');
                        //delete the cache
                        delete fc.cached_editors.field_name;
                        fc.addObjectProperty('field_name');
                        obj.getEditor(path + '.' + i + '.field_name').setValue(currValue);
                    }
                }
            }
            else {
                console.log(data);
            }
            if (editor) {
                dataConfigInitCalls[path] = true;
                checkAllDataConfigInitCalls(editor);
            }
        },
        'json'
    );
}
// ********************************************* DATA CONFIG BUILDER *****************************************





// ********************************************* CONDITION BUILDER *****************************************




// prepare the condition builder and the rules that can be added
async function prepareConditionBuilder(jqueryBuilderJsonInput, monacoEditor) {

    var groups = await getGroups();

    var platforms = await getLookups('pageAccessTypes');
    delete platforms['mobile_and_web']; // remove the combination

    var queryStructure = {
        icons: {
            add_group: 'fas fa-plus-circle',
            add_rule: 'fas fa-plus',
            remove_group: 'fas fa-minus-square',
            remove_rule: 'fas fa-minus-circle',
            error: 'fas fa-exclamation-triangle',
            sortable: 'fas fa-exclamation-triangle'
        },
        operators: $.fn.queryBuilder.constructor.DEFAULTS.operators.concat([
            { type: 'field_equal', optgroup: '', nb_inputs: 2, multiple: false, apply_to: ['string'] },
            { type: 'field_not_equal', optgroup: '', nb_inputs: 2, multiple: false, apply_to: ['string'] },
            { type: 'field_greater', optgroup: '', nb_inputs: 2, multiple: false, apply_to: ['string'] },
            { type: 'field_less', optgroup: '', nb_inputs: 2, multiple: false, apply_to: ['string'] },
            { type: 'field_between', optgroup: '', nb_inputs: 3, multiple: false, apply_to: ['string'] },
            { type: 'between_dates', optgroup: '', nb_inputs: 2, multiple: false, apply_to: ['date'] },
            { type: 'in_one_of', optgroup: '', nb_inputs: 1, multiple: true, apply_to: ['array'] },
        ]),
        lang: {
            operators: {
                field_between: '[Field Name] between [value1(number)] and [value2(number)]',
                field_greater: '[Field Name] > [value(number)]',
                field_less: '[Field Name] < [value(number)]',
                field_equal: '[Field Name] = [value]',
                field_not_equal: '[Field Name] <> [value]',
                between_dates: 'between',
                in_one_of: 'in one of'
            }
        },
        filters: [
            {
                id: 'user_group',
                label: 'User group',
                type: 'string',
                input: 'select',
                multiple: true,
                values: groups,
                plugin: 'selectpicker',
                plugin_config: {
                    liveSearch: true,
                    width: 'auto',
                    liveSearchStyle: 'contains',
                },
                operators: ['in', 'not_in', 'in_one_of']
            }, {
                id: 'field',
                label: 'Field',
                type: 'string',
                input: 'text',
                operators: ['field_equal', 'field_not_equal', 'field_greater', 'field_less', 'field_between']
            }, {
                id: '__current_date__',
                label: 'Current Date',
                type: 'date',
                validation: {
                    format: 'DD-MM-YYYY' // moment.js format
                },
                plugin: 'flatpickr',
                plugin_config: {
                    enableTime: false,
                    dateFormat: 'd-m-Y', // flatpickr format
                    time_24hr: true,
                    weekNumbers: true,
                    locale: {
                        firstDayOfWeek: 1
                    },
                },
                operators: ['equal', 'not_equal', 'less', 'less_or_equal', 'greater', 'greater_or_equal', 'between_dates']
            }, {
                id: '__current_date_time__',
                label: 'Current Datetime',
                type: 'date',
                validation: {
                    format: 'DD-MM-YYYY HH:mm' // moment.js format
                },
                plugin: 'flatpickr',
                plugin_config: {
                    enableTime: true,
                    dateFormat: 'd-m-Y H:i', // flatpickr format
                    time_24hr: true,
                    weekNumbers: true,
                    locale: {
                        firstDayOfWeek: 1
                    },
                },
                operators: ['less', 'less_or_equal', 'greater', 'greater_or_equal', 'between_dates']
            }, {
                id: '__current_time__',
                label: 'Current Time',
                type: 'date',
                validation: {
                    format: 'HH:mm' // moment.js format
                },
                plugin: 'flatpickr',
                plugin_config: {
                    enableTime: true,
                    dateFormat: 'H:i', // flatpickr format
                    time_24hr: true,
                    noCalendar: true,
                },
                operators: ['less', 'less_or_equal', 'greater', 'greater_or_equal', 'between_dates']
            }, {
                id: '__keyword__',
                label: 'Page Keyword',
                type: 'string',
                input: 'text',
                operators: ['equal', 'not_equal']
            }, {
                id: '__platform__',
                label: 'Platform',
                type: 'string',
                input: 'select',
                operators: ['equal'],
                values: platforms,
                plugin: 'selectpicker',
                plugin_config: {
                    liveSearch: true,
                    width: 'auto',
                    liveSearchStyle: 'contains',
                }
            }
        ],
        // rules: rules_basic
    };

    var rules = null;

    try {
        if ($(jqueryBuilderJsonInput).val()) {
            rules = JSON.parse($(jqueryBuilderJsonInput).val());
        } else {
            try {
                var actionConfig = JSON.parse(monacoEditor.getModel().getValue());
                if (actionConfig && actionConfig['condition_jquerBuilderJson']) {
                    rules = actionConfig['condition_jquerBuilderJson'];
                }
            } catch (error) {

            }
        }
    } catch (error) {
        console.log('Rules cannto be parsed');
    }

    if (rules) {
        // load the rules if they exist
        queryStructure['rules'] = rules;
    }

    if ($('.condition_builder').length > 0) {
        $('.condition_builder').queryBuilder(queryStructure);
    } else if ($('.action_condition_builder').length > 0) {
        $('.action_condition_builder').queryBuilder(queryStructure);
    }
}

// show the data config builder
// on click the modal is loaded and show the builder
// on change it updates the monaco editor and the monaco editor updates the input fields
function showConditionBuilder(monacoEditor, jquerBuilderJsonInput) {
    var editor;
    $('.conditionBuilderBtn').each(function () {
        $(this).click(() => {
            $(".condition_builder_modal_holder").modal({
                backdrop: false
            });
            if (editor) {
                // set the latest value if the user changed the JSON manually                
                // editor.setValue(getJson(json));
            }
            $('.condition_builder_modal_holder').on('hidden.bs.modal', function (e) {
                // on modal close set the value to the Monaco editor
            })
            $('.saveConditionBuilder').each(function () {
                $(this).attr('data-dismiss', 'modal');
                $(this).click(function () {
                    var rules = $('.condition_builder').queryBuilder('getRules');
                    $(jquerBuilderJsonInput).val(JSON.stringify(rules));
                    monacoEditor.getModel().setValue(JSON.stringify(rulesToJsonLogic(rules), null, 3));
                })
            });
        });
    });

    // get groups and prepare the consition builder    
    prepareConditionBuilder(jquerBuilderJsonInput, monacoEditor);

}

// ********************************************* CONDITION BUILDER *****************************************

//recursive function to convert the jquery json to JSON logic
function convertRules(rules) {
    var jsonLogic = {};
    if (!rules || !rules["condition"]) {
        alert('Wrong condition!');
        return {};
    }
    jsonLogic[rules.condition] = [];
    rules.rules.forEach(rule => {
        var valuePrefix = rule.field == 'user_group' ? '$' : ''; // if the filed is user group we add the $ prefix
        // get date and time formats if fields need it
        var flatpickrMomentFormat = 'DD-MM-YYYY HH:mm';
        var momentFormat = 'YYYY-MM-DD HH:mm';
        if (rule.field == '__current_date__') {
            flatpickrMomentFormat = 'DD-MM-YYYY';
            momentFormat = 'YYYY-MM-DD';
        } else if (rule.field == '__current_time__') {
            flatpickrMomentFormat = 'HH:mm';
            momentFormat = 'HH:mm';
        }

        if (['in', 'not_in'].includes(rule.operator)) {
            rule.value.forEach(val => {
                var r = {};
                r[jsonLogicOperators[rule.operator].op] = [true, valuePrefix + val]
                jsonLogic[rules.condition].push(r);
            });
        } else if (rule.operator == 'in_one_of') {
            // add additional OR for one of these groups
            var in_one_of = {
                "or": []
            };
            rule.value.forEach(val => {
                var r = {};
                r[jsonLogicOperators[rule.operator].op] = [true, valuePrefix + val]
                in_one_of['or'].push(r);
            });
            jsonLogic[rules.condition].push(in_one_of);
        }
        else if (['field_equal', 'field_not_equal', 'field_greater', 'field_less'].includes(rule.operator)) {
            var r = {};
            r[jsonLogicOperators[rule.operator].op] = [rule.value[0], rule.value[1]]
            jsonLogic[rules.condition].push(r);
        } else if (rule.operator == 'field_between') {
            // add additional AND for one of these groups
            jsonLogic[rules.condition] = {
                "and": []
            }
            jsonLogic[rules.condition]['and'].push({
                ">=": [rule.value[0], rule.value[1]]
            });
            jsonLogic[rules.condition]['and'].push({
                "<=": [rule.value[0], rule.value[2]]
            });
        } else if (rule.operator == 'between_dates') {
            // add additional AND for one of these groups            
            jsonLogic[rules.condition] = {
                "and": []
            }
            jsonLogic[rules.condition]['and'].push({
                ">=": [rule.field, new moment(rule.value[0], flatpickrMomentFormat).format(momentFormat)]
            });
            jsonLogic[rules.condition]['and'].push({
                "<=": [rule.field, new moment(rule.value[1], flatpickrMomentFormat).format(momentFormat)]
            });
        } else if (['equal', 'not_equal', 'less', 'less_or_equal', 'greater', 'greater_or_equal'].includes(rule.operator)) {
            var r = {};
            var val = rule.value;
            if (['__current_date__', '__current_date_time__', '__current_time__'].includes(rule.field)) {
                // if needed convert date
                val = new moment(rule.value, flatpickrMomentFormat).format(momentFormat);
            }
            r[jsonLogicOperators[rule.operator].op] = [rule.field, val]
            jsonLogic[rules.condition].push(r);
        }
        if (rule['rules']) {
            // recursive loop for groups
            jsonLogic[rules.condition].push(convertRules(rule));
        }
    });
    return jsonLogic;
}

// convert queryBuilder rules to JSON logic that we can use
function rulesToJsonLogic(rules) {
    if (rules) {
        rules = JSON.parse(JSON.stringify(rules).replace('"AND"', '"and"').replace('"OR"', '"or"'));
        var jsonLogic = convertRules(rules);
        jsonLogic = JSON.parse(JSON.stringify(jsonLogic).replace('"AND"', '"and"').replace('"OR"', '"or"'));
        return jsonLogic;
    } else {
        return null;
    }
}



// ********************************************* QUALTRICS SURVEY CONFIG BUILDER *****************************************

// show the QualtricsSurvey config builder
// on click the modal is loaded and show the builder
// on change it updates the monaco editor and the monaco editor updates the input fields
function showQualtricsSurveyConfiBuilder(json, monacoEditor) {
    var editor;
    var defValue = getJson(json);
    $('.qualtricsConfigBuilderBtn').each(function () {
        $(this).click(() => {
            $(".qualtricsSurveyConfig_builder_modal_holder").modal({
                backdrop: false
            });
            if (editor) {
                // set the latest value if the user changed the JSON manually                
                editor.setValue(getJson(json));
            }
            $('.qualtricsSurveyConfig_builder_modal_holder').on('hidden.bs.modal', function (e) {

            })
            $('.savequaltricsSurveyConfigBuilder').each(function () {
                $(this).attr('data-dismiss', 'modal');
                // on modal close set the value to the Monaco editor
                $(this).click(function () {
                    monacoEditor.getModel().setValue(JSON.stringify(editor.getValue(), null, 3));
                })
            });
        });
    });
    var schemaUrl = window.location.protocol + "//" + window.location.host + BASE_PATH + "/schemas/qualtricsSurveyConfig/qualtricsSurveyConfig.json";
    // get the schema with AJAX call
    $.ajax({
        dataType: "json",
        url: schemaUrl,
        success: (s) => {
            editor = new JSONEditor($('.qualtricsSurveyConfig_builder')[0], {
                theme: 'bootstrap4',
                iconlib: 'fontawesome5',
                ajax: true,
                schema: s,
                startval: defValue,
                show_errors: "always",
                display_required_only: true
            });
        }
    });
}

// ********************************************* QUALTRICS SURVEY CONFIG BUILDER *****************************************

// ********************************************* Action CONFIG BUILDER *****************************************

// show the action config builder
// on click the modal is loaded and show the builder
// on change it updates the monaco editor and the monaco editor updates the input fields
function showActionConfiBuilder(json, monacoEditor) {
    var editor;
    var defValue = getJson(json);
    $('.actionConfigBuilderBtn').each(function () {
        $(this).click(() => {
            $(".actionConfig_builder_modal_holder").modal({
                backdrop: false
            });
            if (editor) {
                // set the latest value if the user changed the JSON manually                
                editor.setValue(getJson(json));
            }
            $('.actionConfig_builder_modal_holder').on('hidden.bs.modal', function (e) {

            })
            $('.saveActionConfigBuilder').each(function () {
                $(this).attr('data-dismiss', 'modal');
                // on modal close set the value to the Monaco editor
                $(this).click(function () {
                    var val = editor.getValue();
                    if (val['condition'] && !(val['condition'] instanceof Object)) {
                        val['condition'] = JSON.parse(val['condition']);
                    }
                    if (val['condition_jquerBuilderJson'] && !(val['condition_jquerBuilderJson'] instanceof Object)) {
                        val['condition_jquerBuilderJson'] = JSON.parse(val['condition_jquerBuilderJson']);
                    }
                    monacoEditor.getModel().setValue(JSON.stringify(val, null, 3));
                })
            });
        });
    });
    var schemaUrl = window.location.protocol + "//" + window.location.host + BASE_PATH + "/schemas/actionConfig/actionConfig.json";
    // get the schema with AJAX call
    $.ajax({
        dataType: "json",
        url: schemaUrl,
        success: (s) => {
            editor = new JSONEditor($('.actionConfig_builder')[0], {
                theme: 'bootstrap4',
                iconlib: 'fontawesome5',
                ajax: true,
                schema: s,
                show_errors: "always",
            });
            editor.on('change', () => {
                $('.actionConfig_builder').find('select').each(function () {
                    $(this).selectpicker();
                    $(this).selectpicker('refresh');
                })
            });
            getGroupsForActionConfig(editor, defValue, editor);
        }
    });
}

// adjust groups for action config
async function getGroupsForActionConfig(obj, defValue, editor) {
    var groups = await getGroups();
    var fc = obj.getEditor('root.group').parent;
    fc.original_schema.properties.group.items["enum"] = groups;
    fc.schema.properties.group.items["enum"] = groups;
    fc.removeObjectProperty('group');
    //delete the cache
    delete fc.cached_editors.group;
    fc.addObjectProperty('group');
    editor.setValue(defValue);
}

function showActionConditionBuilder(monacoEditor, jquerBuilderJsonInput) {
    var editor;
    $('.actionConfigConditionBuilderBtn').each(function () {
        $(this).click(() => {
            $(".action_condition_builder_modal_holder").modal({
                backdrop: false
            });
            if (editor) {
                // set the latest value if the user changed the JSON manually                
                // editor.setValue(getJson(json));
            }
            $('.action_condition_builder_modal_holder').on('hidden.bs.modal', function (e) {
                // on modal close set the value to the Monaco editor
            })
            $('.saveActionConditionBuilder').each(function () {
                $(this).attr('data-dismiss', 'modal');
                $(this).click(function () {
                    var rules = $('.action_condition_builder').queryBuilder('getRules');
                    var configVal = {};
                    try {
                        configVal = JSON.parse(monacoEditor.getModel().getValue());
                    } catch (error) {

                    }
                    configVal['condition'] = rulesToJsonLogic(rules);
                    configVal['condition_jquerBuilderJson'] = rules;
                    monacoEditor.getModel().setValue(JSON.stringify(configVal, null, 3));
                })
            });
        });
    });

    // get groups and prepare the consition builder
    prepareConditionBuilder(jquerBuilderJsonInput, monacoEditor);
}

// ********************************************* ACTION CONFIG BUILDER *****************************************


//********************************************** FUNCTIONS *****************************************************

async function getGroups() {
    var groups = [];
    jQuery.ajax({
        url: BASE_PATH + '/request/AjaxDataSource/get_groups',
        async: false,
        cache: false,
        dataType: "json",
        success: function (data) {
            if (data.success) {
                try {
                    groups = JSON.parse(data.data);
                } catch (error) {
                    console.log('Error while parsing JSON', data.data);
                }
            }
            else {
                console.log(data);
            }
        }
    });
    return groups;
}

async function getLookups(lookupType) {
    var lookups = [];
    jQuery.ajax({
        url: BASE_PATH + '/request/AjaxDataSource/get_lookups',
        async: false,
        cache: false,
        type: 'post',
        data: { lookupType: lookupType },
        dataType: "json",
        success: function (data) {
            if (data.success) {
                try {
                    lookups = JSON.parse(data.data);
                } catch (error) {
                    console.log('Error while parsing JSON', data.data);
                }
            }
            else {
                console.log(data);
            }
        }
    });
    return lookups;
}

//********************************************** FUNCTIONS *****************************************************