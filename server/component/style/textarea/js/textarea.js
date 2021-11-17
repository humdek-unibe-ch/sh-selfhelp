dataConfigInitCalls = {};

$(document).ready(function () {
    autosize($('textarea'));
    check_textarea_locked_after_submit();
    $('.json').each(function () {
        // load the monaco editor for json fields
        require.config({ paths: { vs: BASE_PATH + '/js/ext/vs' } });
        var json = $(this)[0];
        require(['vs/editor/editor.main'], function () {
            var model = null;
            if ($(json).prev().attr('name').includes('data_config')) {
                model = setDataConfigSchema(monaco, json);
            } else if ($(json).prev().attr('name').includes('condition')) {
                model = setConditionSchema(monaco, json);
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
            }
        });
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
                "title": "dataConfig/dataConfig Schema",
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
    monaco.languages.json.jsonDefaults.setDiagnosticsOptions({
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
        console.log('Error parsing the data_config value!');
        return {};
    }
}

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
                // on modal close set the value to the Monaco editor
                monacoEditor.getModel().setValue(JSON.stringify(editor.getValue(), null, 3));
            })
            $('.closeModal').each(function (){
                $(this).attr('data-dismiss', 'modal');
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
                            console.log('new field', key);
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
                        editor.watch(key, watcherCallback.bind(editor, key)); // add the keys again - it is used to add watches for new sources
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

const watcherCallback = function (path) {
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
                editor.watch(key, watcherCallback.bind(editor, key)); // add watch events to all exisitng keys containing [table, type, field_name]
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