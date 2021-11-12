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
    if (contentHeight < 500) {
        $(object).height(contentHeight);
        editor.layout();
    }
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
        return null;
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
        });
    });
    var schemaUrl = window.location.protocol + "//" + window.location.host + BASE_PATH + "/schemas/dataConfig/dataConfig.json";
    // get the schema with AJAX call
    $.ajax({
        dataType: "json",
        url: schemaUrl,
        success: (s) => {
            // on success prepare the builder
            editor = new JSONEditor($('.data_config_builder')[0], {
                theme: 'bootstrap4',
                iconlib: 'fontawesome5',
                ajax: true,
                schema: s,
                startval: defValue
            });
            editor.on('change', () => {
                //on change format the code and propagate the values
                monacoEditor.getModel().setValue(JSON.stringify(editor.getValue(), null, 3));
                calcMonacoEditorSize(monacoEditor, json);
                monacoEditor.getAction('editor.action.formatDocument').run().then(() => {
                    calcMonacoEditorSize(monacoEditor, json);
                });
                var errors = editor.validate();
                if (errors.length) {
                    console.log(errors);
                }
            });
        }
    });
}