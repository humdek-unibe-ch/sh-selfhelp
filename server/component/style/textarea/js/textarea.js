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
        var jsonMetaField = $('input[name*="[' + jsonFieldName + ']"][name*="[meta]"]');
        console.log('meta', jsonFieldName, jsonMetaField);
        var meta = {};
        try {
            meta = JSON.parse(jsonMetaField.val());
        } catch (error) {
            console.log('Meta for ' + jsonFieldName + ' cannot be parsed');
        }
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
            var jsonModalErrorStatusField = $(this).find('.json-mapper-error-status')[0];
            var jsonMappedItems = $(this).find('.json_mapped_items')[0];
            var jsonTree = $(this).find('.json_tree')[0];
            var jsonTreePath = $(this).find('.json_tree_path');
            console.log(jsonTreePath);
            reloadMappedItems(meta, jsonMappedItems); // load the existing values
            $(jsonModalHolder).modal({
                backdrop: false
            });
            var saveMapperBtn = $(this).find('.saveJsonMapper')[0];
            $(saveMapperBtn).attr('data-dismiss', 'modal');
            $(jsonModalTitleField).html(jsonFieldName);
            var jsonData = {};
            try {
                jsonData = JSON.parse(jsonValueField.val());
                const jsTreeData = transformToJsTreeFormat(jsonData, '')['children'];
                console.log(jsTreeData);
                console.log(jsonData);
                $(jsonTree).jstree({
                    core: {
                        data: jsTreeData,
                        themes: {
                            icons: false
                        }
                    }
                });
                $(jsonTree).on('select_node.jstree', function (e, data) {
                    // Get the clicked node
                    var clickedNode = data.node;

                    // Access the 'value' property of the clicked node
                    var nodeValue = clickedNode.original;
                    if (!(nodeValue.text in meta)) {
                        meta[nodeValue.text] = "";
                    }
                    // Do something with the node value
                    reloadMappedItems(meta, jsonMappedItems);
                    console.log('Clicked Node Value:', nodeValue, clickedNode);
                });
            } catch (error) {

            }
            $(saveMapperBtn).off('click').click(function () {
                console.log(meta);
                $(jsonMetaField).val(JSON.stringify(meta));
                $(jsonMetaField).trigger('change');
            })
        });

    })
}

/**
 * Removes the last dot ('.') character from the end of a string if present.
 *
 * @param {string} inputString - The input string that may contain a trailing dot.
 * @returns {string} - The modified string with the trailing dot removed, or the original string if no dot is found.
 */
function removeLastDot(inputString) {
    if (inputString.endsWith('.')) {
        return inputString.slice(0, -1); // Remove the last character
    }
    return inputString; // Return unchanged if it doesn't end with a dot
}

/**
 * Transforms a nested object into a jsTree-compatible data structure while modifying text and value properties.
 *
 * @param {Object} obj - The input nested object to transform.
 * @param {string} [path=''] - The path to the current object (used for building text and value properties). 
 * @returns {Object} - The transformed jsTree-compatible data structure.
 */
function transformToJsTreeFormat(obj, path = '') {
    const jsTreeData = {
        text: removeLastDot(path), // Use the provided path or rootNodeName as the text
        value: removeLastDot(path),
        children: []
    };

    for (let key in obj) {
        if (typeof obj[key] === 'object') {
            // Recursively process nested objects
            const childNode = transformToJsTreeFormat(obj[key], path + key + '.');
            jsTreeData.children.push(childNode);
        } else {
            // Add leaf node
            jsTreeData.children.push({
                text: path + key,
                value: (path + key)
            });
        }
    }

    return jsTreeData;
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

/**
 * Reloads and updates a list of mapped items displayed in a container.
 *
 * @param {Object} meta - The meta object containing item data.
 * @param {jQuery|HTMLElement} jsonMappedItems - The container element where mapped items are displayed.
 */
function reloadMappedItems(meta, jsonMappedItems) {
    $(jsonMappedItems).empty(); // cleat the mapped items
    for (let item in meta) {
        if (meta.hasOwnProperty(item)) {
            var row = $('<div/>').addClass('d-flex align-items-center bg-white m-2 p-2 border rounded text-dark');
            var label = $('<label/>').text(item).addClass('mb-0 pl-2 pr-2 mr-2 font-weight-bold');
            var input = $('<input/>').attr('type', 'text').addClass('rounded border ml-auto border-dark pl-2 pr-2');
            input.val(meta[item]);
            input.change(function () {
                // on change add the value in the meta
                var inputValue = $(this).val();
                var currentItem = item;
                meta[currentItem] = inputValue;
            })
            var closeBtn = $('<i/>').addClass("far fa-window-close text-danger fa-lg pointer mr-2");
            closeBtn.click(function () { 
                console.log('click');
                var currentItem = item;
                $(this).parent().remove(); // remove the row on click
                delete meta[currentItem]; // remove the value from the meta object        
            });
            row.append(closeBtn);
            row.append(label);
            row.append(input);
            $(jsonMappedItems).append(row);
        }
    }
}