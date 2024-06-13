var dataConfigInitCalls = {};
const dataConfigInit = 'dataConfigInit'
var dataConfigEditor;

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
                    var val = JSON.stringify(dataConfigEditor.getValue(), null, 3);
                    if (val == '[]') {
                        val = '';
                    }
                    $(data_config).val(val);
                    $(data_config).trigger('change');
                    $('.dataConfigBuilderBtn').removeClass('btn-primary btn-warning');
                    if (dataConfigEditor.getValue().length > 0) {
                        $('.dataConfigBuilderBtn').addClass('btn-warning');
                        $('.dataConfigBuilderBtn').html('Edit Data Config');
                    } else {
                        $('.dataConfigBuilderBtn').addClass('btn-primary');
                        $('.dataConfigBuilderBtn').html('Add Data Config');
                    }
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
                dataConfigEditor = new JSONEditor(dataConfigBuilder, {
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
                dataConfigEditor.off("change").on("change", function () {
                    for (let key in dataConfigEditor.editors) {
                        if (dataConfigEditor.editors.hasOwnProperty(key) && key !== 'root' && dataConfigEditor.watchlist && !dataConfigEditor.watchlist[key] &&
                            (key.includes('table') || key.includes('retrieve') || key.includes('field_name'))) {
                            if (key.includes('retrieve')) {
                                // populate tables for new data source                                                                
                                getTableNames(dataConfigEditor, key.replace('retrieve', 'table'));
                            } else if (key.includes('field_name')) {
                                // populate field names for the new field
                                var keys = key.split('.');
                                var dataSourceKey = keys[0] + '.' + keys[1];
                                getTableFieldNames(
                                    this.getEditor(dataSourceKey + '.table').getValue(),
                                    this,
                                    key,
                                    true
                                );
                            }
                            dataConfigEditor.watch(key, dataConfigWatcherCallback.bind(dataConfigEditor, key)); // add the keys again - it is used to add watches for new sources
                        }
                    }
                })

                dataConfigEditor.on('ready', () => {
                    // Initialization
                    for (let key in dataConfigEditor.editors) {
                        if (dataConfigEditor.editors.hasOwnProperty(key) && key !== 'root') {
                            if (key.includes('retrieve')) {
                                // populate tables
                                var k = key.replace('retrieve', 'table');
                                dataConfigInitCalls[k] = false;
                                getTableNames(dataConfigEditor, key.replace('retrieve', 'table'), dataConfigEditor);
                            } else if (key.includes('table') && dataConfigEditor.getEditor(key) && dataConfigEditor.getEditor(key).getValue()) {
                                // populate fields
                                var k = key.replace('retrieve', 'table');
                                dataConfigInitCalls[k] = false;
                                getTableFieldNames(
                                    dataConfigEditor.getEditor(key).getValue(),
                                    dataConfigEditor,
                                    key.replace('table', 'fields'),
                                    false,
                                    dataConfigEditor
                                );
                                getTableFieldNames(
                                    dataConfigEditor.getEditor(key).getValue(),
                                    dataConfigEditor,
                                    key.replace('table', 'map_fields'),
                                    false,
                                    dataConfigEditor
                                );
                            }
                        }
                    }
                    dataConfigEditor.on('change', () => {
                        $('.data_config_builder').find('select[name*="[field_name]"], select[name*="[table]"]').each(function () {
                            $(this).data('live-search', true);
                            $(this).selectpicker();
                            $(this).selectpicker('refresh');
                        })
                    });
                });
            }
        });
    });
}

// ********************************************* DATA CONFIG BUILDER *****************************************

const dataConfigWatcherCallback = function (path) {
    if (path.includes('retrieve') && this.getEditor(path) && this.getEditor(path).getValue()) {
        getTableNames(this, path.replace('retrieve', 'table'));
    } else if (path.includes('table') && this.getEditor(path) && this.getEditor(path).getValue()) {
        getTableFieldNames(
            this.getEditor(path).getValue(),
            this,
            path.replace('table', 'fields'),
            false
        );
        getTableFieldNames(
            this.getEditor(path).getValue(),
            this,
            path.replace('table', 'map_fields'),
            false
        );
    }
}

function checkAllDataConfigInitCalls(dataConfigEditor) {
    let result = true;
    for (const key in dataConfigInitCalls) {
        result = dataConfigInitCalls[key] && result;
    }
    if (result) {
        // all calls are done
        for (let key in dataConfigEditor.editors) {
            if (dataConfigEditor.editors.hasOwnProperty(key) && key !== 'root' &&
                (key.includes('table') || key.includes('retrieve') || key.includes('field_name'))) {
                dataConfigEditor.watch(key, dataConfigWatcherCallback.bind(dataConfigEditor, key)); // add watch events to all existing keys containing [table, type, field_name]
            }
        }
    }
}

// AJAX call to get the table names
function getTableNames(obj, path, dataConfigEditor) {
    $.post(
        BASE_PATH + '/request/AjaxDataSource/get_table_names',
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
                fc.original_schema.properties.table["enumSource"] = prepareEnumSource(tableNames);
                fc.schema.properties.table["enumSource"] = prepareEnumSource(tableNames);
                fc.removeObjectProperty('table');
                //delete the cache
                delete fc.cached_editors.table;
                fc.addObjectProperty('table');
                obj.getEditor(path).setValue(currValue);
            }
            else {
                console.log(data);
            }
            if (dataConfigEditor) {
                dataConfigInitCalls[path] = true;
                checkAllDataConfigInitCalls(dataConfigEditor);
            }
        },
        'json'
    );
}

// AJAX call to get the table fields
function getTableFieldNames(formName, obj, path, init, dataConfigEditor) {
    $.post(
        BASE_PATH + '/request/AjaxDataSource/get_table_fields',
        {
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
                } else if (obj.getEditor(path)) {
                    for (let i = 0; i < obj.getEditor(path).rows.length; i++) {
                        var currValue = obj.getEditor(path + '.' + i + '.field_name').getValue()?.valueOf();
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
            if (dataConfigEditor) {
                dataConfigInitCalls[path] = true;
                checkAllDataConfigInitCalls(dataConfigEditor);
            }
        },
        'json'
    );
}
// ********************************************* DATA CONFIG BUILDER *****************************************
