$(document).ready(function () {
    autosize($('textarea'));
    check_textarea_locked_after_submit();
    // initJsonFields();
});

function initJsonFields() {
    $('.json').each(function () {
        // load the monaco editor for json fields
        require.config({ paths: { vs: BASE_PATH + '/js/ext/vs' } });
        var json = $(this)[0];
        if ($(json).prev().attr('name').includes('jquery_builder_json')) {
            // this field is hidden and a holder only
            // $(json).parent().parent().addClass('d-none'); //hide the label
            return;
        } else {
            // if ($(json).prev().attr('name').includes('condition') || $(json).prev().attr('name').includes('data_config')) {
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
                } else if ($(json).prev().parent().attr('class') && $(json).prev().parent().attr('class').includes('qualtricsSurveyConfig')) {
                    showQualtricsSurveyConfiBuilder(json, editor);
                } else if ($(json).prev().parent().attr('class') && $(json).prev().parent().attr('class').includes('actionConfig')) {
                    showActionConfiBuilder(json, editor);
                    var jqueryBuilderJsonInput;
                    $('textarea').each(function () {
                        if ($(this).attr('class') && $(this).attr('class').includes('action_condition_builder')) {
                            jqueryBuilderJsonInput = this;
                        }
                    })
                    showConditionBuilder(editor, jqueryBuilderJsonInput);
                    showActionConditionBuilder(editor, jqueryBuilderJsonInput);
                }
            });
        }
    })
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

