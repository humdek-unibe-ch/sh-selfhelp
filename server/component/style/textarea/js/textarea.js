const jsonEditorInit = 'jsonEditorInit'
const cssEditorInit = 'cssEditorInit'
const mdInit = 'mdInit'
var simpleMDEEidtorRefreshed = false;

$(document).ready(function () {
    autosize($('textarea'));
    check_textarea_locked_after_submit();
    initTextarea();
});

function initTextarea() {
    initJsonFields();
    initMarkdownFields();
    initCssFields();
}

function initJsonFields() {
    $('.json-mapping').each(function () {
        // load the monaco editor for json fields
        var jsonMappingButton = $(this).find('.json-mapping-btn')[0];
        var jsonElement = $(this).find('.json')[0];
        var jsonFieldName = $(jsonMappingButton).data('name');
        var jsonValueField = $('textarea[name*="[' + jsonFieldName + ']"][name*="[content]"]');
        if ($(jsonMappingButton).data(jsonEditorInit)) {
            // already initialized do not do it again
            return;
        }
        $(jsonMappingButton).data(jsonEditorInit, true);
        require.config({ paths: { vs: BASE_PATH + '/js/ext/vs' } });

        require(['vs/editor/editor.main'], function () {
            var editorOptions = {
                value: jsonValueField.val(),
                language: 'json',
                automaticLayout: true,
                renderLineHighlight: "none"
            }
            var editorConfig = monaco.editor.create(jsonElement, editorOptions);
            editorConfig.getAction('editor.action.formatDocument').run().then(() => {
                calcMonacoEditorSize(editorConfig, jsonElement);
            });
            editorConfig.onDidChangeModelContent(function (e) {
                $(jsonValueField).val(editorConfig.getValue());
                calcMonacoEditorSize(editorConfig, jsonElement);
                $(jsonValueField).trigger('change');
            });
        });

        $(jsonMappingButton).off('click').click(() => {
            var jsonModalHolder = $(this).find('.json_mapper_modal_holder')[0];
            var jsonModalTitleField = $(this).find('.json-mapper-title-field')[0];
            $(jsonModalHolder).modal({
                backdrop: false
            });
            var saveMapperBtn = $(this).find('.saveJsonMapper')[0];
            $(saveMapperBtn).attr('data-dismiss', 'modal');
            $(jsonModalTitleField).html(jsonFieldName);
            $(saveMapperBtn).off('click').click(function () {
                // var rules = $('.condition_builder').queryBuilder('getRules');
                // $(meta).val(JSON.stringify(rules));
                // $(condition).val(JSON.stringify(rulesToJsonLogic(rules), null, 3));
                // $(condition).trigger('change');
                // $('.conditionBuilderBtn').removeClass('btn-primary btn-warning');
                // if (rules) {
                //     $('.conditionBuilderBtn').addClass('btn-warning');
                //     $('.conditionBuilderBtn').html('Edit Condition');
                // } else {
                //     $('.conditionBuilderBtn').addClass('btn-primary');
                //     $('.conditionBuilderBtn').html('Add Condition');
                // }
            })
        });

    })
}

function check_textarea_locked_after_submit() {
    // check if the text area should be locked after submit
    $('.selfhelpTextArea').each(function () {
        if ($(this).data('locked_after_submit')) {
            $(this).prop('readonly', true);
        }
    })
}


function initMarkdownFields() {
    var markdowns = $('.style-markdown');
    Array.from(markdowns).forEach((md) => {
        if ($(md).data(mdInit)) {
            // already initialized do not do it again
            return;
        }
        $(md).data(mdInit, true);
        var editor = new EasyMDE({
            element: md,
            autoDownloadFontAwesome: false,
            spellChecker: false,
            autoRefresh: { delay: 0 },
            forceSync: true,
            toolbar: ["bold", "italic", "heading", "quote", "unordered-list", "ordered-list", "link", "image", "table", "preview", "guide"],
            renderingConfig: {
                singleLineBreaks: false
            }
        });
        editor.codemirror.on("change", () => {
            unsavedChanges.push(this);
        });
        editor.codemirror.on("cursorActivity", () => {
            if (!simpleMDEEidtorRefreshed) {
                simpleMDEEidtorRefreshed = true;
                editor.codemirror.refresh();
            }
        });
    });
}

function initCssFields() {
    $('.css').each(function () {
        // load the monaco editor for css fields
        var cssField = $(this)[0];
        if ($(cssField).data(cssEditorInit)) {
            // already initialized do not do it again
            return;
        }
        $(cssField).data(cssEditorInit, true);
        require.config({ paths: { vs: BASE_PATH + '/js/ext/vs' } });
        require(['vs/editor/editor.main'], function () {
            var editorOptions = {
                value: $(cssField).prev().val(),
                language: 'css',
                automaticLayout: true,
                renderLineHighlight: "none"
            }
            var editorConfig = monaco.editor.create(cssField, editorOptions);
            editorConfig.getAction('editor.action.formatDocument').run().then(() => {
                calcMonacoEditorSize(editorConfig, cssField);
            });
            editorConfig.onDidChangeModelContent(function (e) {
                $(cssField).prev().val(editorConfig.getValue());
                calcMonacoEditorSize(editorConfig, cssField);
                $(cssField).prev().trigger('change');
            });
            cssFormatMonaco(monaco);
        });
    })
}