const jsonEditorInit = 'jsonEditorInit'
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
}

function initJsonFields() {
    $('.json').each(function () {
        // load the monaco editor for json fields
        var json = $(this)[0];
        if ($(json).data(jsonEditorInit)) {
            // already initialized do not do it again
            return;
        }
        $(json).data(jsonEditorInit, true);
        require.config({ paths: { vs: BASE_PATH + '/js/ext/vs' } });

        require(['vs/editor/editor.main'], function () {
            var editorOptions = {
                value: $(json).prev().val(),
                language: 'json',
                automaticLayout: true,
                renderLineHighlight: "none"
            }
            var editorConfig = monaco.editor.create(json, editorOptions);
            editorConfig.getAction('editor.action.formatDocument').run().then(() => {
                calcMonacoEditorSize(editorConfig, json);
            });
            editorConfig.onDidChangeModelContent(function (e) {
                $(json).prev().val(editorConfig.getValue());
                calcMonacoEditorSize(editorConfig, json);
                $(json).prev().trigger('change');
            });
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
            minHeight: '10px',
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