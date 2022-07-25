var dataConfigInitCalls = {};
const dataConfigInit = 'dataConfigInit'

$(document).ready(function () {
    initDataConfigBuilder();
});

function initDataConfigBuilder() {

    $('.dataConfigBuilderBtn').each(function () {
        var data_config = $("textarea[name*='data_config']")[0];
        $(this).off('click').click(() => {
            $(".data_config_builder_modal_holder").modal({
                backdrop: false
            });
            $('.saveDataConfig').each(function () {
                $(this).attr('data-dismiss', 'modal');
                // on modal close set the value to the Monaco editor
                $(this).click(function () {
                    var val = JSON.stringify(editor.getValue(), null, 3);
                    if(val == '[]'){
                        val = '';
                    }
                    $(data_config).val(val);
                    $(data_config).trigger('change');
                })
            });
        });

        var dataConfigBuilder = $('.data_config_builder')[0];
        if ($(dataConfigBuilder).data(dataConfigInit)) {
            // already initialized do not do it again
            return;
        }
        $(dataConfigBuilder).data(dataConfigInit, true);             
        var schemaUrl = window.location.protocol + "//" + window.location.host + BASE_PATH + "/schemas/dataConfig/dataConfig.json";
        // get the schema with AJAX call
        var textarea_json_val = null;
        try {
            textarea_json_val = JSON.parse($(data_config).val());
        } catch (error) {
            textarea_json_val = null;
        }
        $.ajax({
            dataType: "json",
            url: schemaUrl,
            success: (s) => {
                var editor = new JSONEditor(dataConfigBuilder, {
                    theme: 'bootstrap4',
                    input_size: "small",
                    custom_forms: false,
                    object_indent: false,
                    table_border: true,
                    table_zebrastyle: true,
                    tooltip: "bootstrap",
                    iconlib: 'fontawesome5',
                    ajax: true,
                    schema: s,
                    startval: textarea_json_val,
                    show_errors: "always",
                    display_required_only: true,
                    compact: true,
                });

                // check for new data source or new fields additions
                editor.off("change").on("change", function () {
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
    });
}

// ********************************************* DATA CONFIG BUILDER *****************************************

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
                editor.watch(key, dataConfigWatcherCallback.bind(editor, key)); // add watch events to all existing keys containing [table, type, field_name]
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
                var currValue = obj.getEditor(path).getValue().valueOf();
                var fc = obj.getEditor(path).parent;
                fc.original_schema.properties.table["enum"] = tableNames;
                fc.schema.properties.table["enum"] = tableNames;
                fc.removeObjectProperty('table');
                //delete the cache
                delete fc.cached_editors.table;
                fc.addObjectProperty('table');
                obj.getEditor(path).setValue(currValue);
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
