$(document).ready(function () {
    initActionConfigBuilder();
});

function initActionConfigBuilder() {
    $('.actionConfigBuilderMonaco').each(function () {
        // load the monaco editor for json fields
        require.config({ paths: { vs: BASE_PATH + '/js/ext/vs' } });
        var json = $(this)[0];

        require(['vs/editor/editor.main'], function () {
            var model = null;
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
            showActionConfigBuilder(json, editor);
            showActionConditionBuilder(editor, null);

        });
    })
}

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

function setConditionSchema(monaco, json) {
    // get the json ligic schemes
    var schema = window.location.protocol + "//" + window.location.host + BASE_PATH + "/schemas/json-logic/json-logic.json";
    var modelUri = monaco.Uri.parse(schema); // a made up unique URI for our model
    if (typeof monaco != "undefined") {
        monaco.editor.getModels().forEach(model => model.dispose()); // first clear the loaded editors
    }
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

// ********************************************* Action CONFIG BUILDER *****************************************

// show the action config builder
// on click the modal is loaded and show the builder
// on change it updates the monaco editor and the monaco editor updates the input fields
function showActionConfigBuilder(json, monacoEditor) {
    var editor;
    var defValue = getActionConfigJson(json);
    $('.actionConfigBuilderBtn').each(function () {
        $(this).click(() => {
            $(".actionConfig_builder_modal_holder").modal({
                backdrop: false
            });
            if (editor) {
                // set the latest value if the user changed the JSON manually                
                editor.setValue(getActionConfigJson(json));
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

            editor.on('ready', () => {
                editor.on('change', () => {
                    $('.actionConfig_builder').find('select').each(function () {
                        $(this).selectpicker();
                        $(this).selectpicker('refresh');
                    })
                });
                getGroupsForActionConfig(editor, defValue, editor);
            });
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

function showActionConditionBuilder(monacoEditor, jqueryBuilderJsonInput) {
    var editor;
    $('.actionConfigConditionBuilderBtn').each(function () {
        $(this).click(() => {
            var rules2 = $('.action_condition_builder').queryBuilder('getRules');
            $(".action_condition_builder_modal_holder").modal({
                backdrop: false
            });
            if (editor) {
                // set the latest value if the user changed the JSON manually                
                // editor.setValue(getJson(jqueryBuilderJsonInput));
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

    // get groups and prepare the condition builder
    prepareConditionBuilder(jqueryBuilderJsonInput, monacoEditor); //function in conditionBuilder.js
}

// ********************************************* ACTION CONFIG BUILDER *****************************************


function getActionConfigJson(json) {
    try {
        var res = JSON.parse($(json).prev().val());
        return res;
    } catch (error) {
        return null;
    }
}