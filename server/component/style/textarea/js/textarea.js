$(document).ready(function () {
    autosize($('textarea'));
    check_textarea_locked_after_submit();
    $('.json').each(function () {
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
        });
    })
});

function calcMonacoEditorSize(editor, object) {
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
    $('.selfhelpTextArea').each(function () {
        if ($(this).data('locked_after_submit') && $(this).val()) {
            $(this).prop('readonly', true);
        }
    })
}
