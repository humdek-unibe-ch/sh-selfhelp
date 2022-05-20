$(document).ready(function () {
    initQualtricsSurveyConfigBuilder();
});

function initQualtricsSurveyConfigBuilder() {
    $('.qualtricsConfigBuilderMonaco').each(function () {
        // load the monaco editor for json fields
        require.config({ paths: { vs: BASE_PATH + '/js/ext/vs' } });
        var json = $(this)[0];

        require(['vs/editor/editor.main'], function () {
            var modelQualtrics = setQualtricsSurveyConfigSchema(monaco, json);
            var editorOptions = {
                value: $(json).prev().val(),
                language: 'json',
                automaticLayout: true,
                renderLineHighlight: "none"
            }
            if (modelQualtrics) {
                editorOptions['model'] = modelQualtrics;
            }
            var editorQualtricsConfig = monaco.editor.create(json, editorOptions);
            editorQualtricsConfig.getAction('editor.action.formatDocument').run().then(() => {
                calcMonacoEditorSize(editorQualtricsConfig, json);
            });
            editorQualtricsConfig.onDidChangeModelContent(function (e) {
                $(json).prev().val(editorQualtricsConfig.getValue());
                calcMonacoEditorSize(editorQualtricsConfig, json);
            });
            showQualtricsSurveyConfigBuilder(json, editorQualtricsConfig);
        });
    })
}

// ********************************************* QUALTRICS SURVEY CONFIG BUILDER *****************************************

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

// show the QualtricsSurvey config builder
// on click the modal is loaded and show the builder
// on change it updates the monaco editor and the monaco editor updates the input fields
function showQualtricsSurveyConfigBuilder(json, monacoEditor) {
    var editor;
    var defValue = getQualtricsConfigJson(json);
    $('.qualtricsConfigBuilderBtn').each(function () {
        $(this).click(() => {
            $(".qualtricsSurveyConfig_builder_modal_holder").modal({
                backdrop: false
            });
            if (editor) {
                // set the latest value if the user changed the JSON manually                
                editor.setValue(getQualtricsConfigJson(json));
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

function getQualtricsConfigJson(json){
    try {
        var res = JSON.parse($(json).prev().val());
        return res;
    } catch (error) {
        return null;
    }
}

// ********************************************* QUALTRICS SURVEY CONFIG BUILDER *****************************************