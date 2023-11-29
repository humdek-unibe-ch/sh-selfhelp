// jsonLogic export
const jsonLogicOperators = {
    field_equal: { op: '==' },
    field_not_equal: { op: '!=' },
    field_greater: { op: '>' },
    field_less: { op: '<' },
    equal: { op: '==' },
    not_equal: { op: '!=' },
    greater: { op: '>' },
    less: { op: '<' },
    less_or_equal: { op: '<=' },
    greater_or_equal: { op: '>=' },
    not_in: { op: '!=' },
    in: { op: '==' },
    in_one_of: { op: '==' },
};

$(document).ready(function () {
    initConditionBuilder();
});

function initConditionBuilder() {
    var condBuilderBtns = $('.conditionBuilderBtn');
    if (condBuilderBtns.length > 0) {
        var meta = $('input[name^="fields[condition]"][name$="[meta]"]')[0];
        var condition = $("textarea[name*='condition']")[0];
        condBuilderBtns.each(function () {
            $(this).off('click').click(() => {
                $(".condition_builder_modal_holder").modal({
                    backdrop: false
                });
                $('.saveConditionBuilder').each(function () {
                    $(this).attr('data-dismiss', 'modal');
                    $(this).off('click').click(function () {
                        var rules = $('.condition_builder').queryBuilder('getRules');
                        $(meta).val(JSON.stringify(rules));
                        $(condition).val(JSON.stringify(rulesToJsonLogic(rules), null, 3));
                        $(condition).trigger('change');
                        $('.conditionBuilderBtn').removeClass('btn-primary btn-warning');
                        if (rules) {
                            $('.conditionBuilderBtn').addClass('btn-warning');
                            $('.conditionBuilderBtn').html('Edit Condition');
                        } else {
                            $('.conditionBuilderBtn').addClass('btn-primary');
                            $('.conditionBuilderBtn').html('Add Condition');
                        }
                    })
                });
            });
        });

        // get groups and prepare the condition builder    
        prepareConditionBuilder($(meta).val());
    }

    // ********************************************* CONDITION BUILDER *****************************************
}



// prepare the condition builder and the rules that can be added
async function prepareConditionBuilder(jqueryBuilderJsonInput) {

    var groups = await getGroups();

    var platforms = await getLookups('pageAccessTypes');
    delete platforms['mobile_and_web']; // remove the combination

    var languages = await getLanguages();
    delete languages['0000000001']; // remove all languages selection

    var queryStructure = {
        icons: {
            add_group: 'fas fa-plus-circle',
            add_rule: 'fas fa-plus',
            remove_group: 'fas fa-minus-square',
            remove_rule: 'fas fa-minus-circle',
            error: 'fas fa-exclamation-triangle',
            sortable: 'fas fa-exclamation-triangle'
        },
        operators: $.fn.queryBuilder.constructor.DEFAULTS.operators.concat([
            { type: 'field_equal', optgroup: '', nb_inputs: 2, multiple: false, apply_to: ['string'] },
            { type: 'field_not_equal', optgroup: '', nb_inputs: 2, multiple: false, apply_to: ['string'] },
            { type: 'field_greater', optgroup: '', nb_inputs: 2, multiple: false, apply_to: ['string'] },
            { type: 'field_less', optgroup: '', nb_inputs: 2, multiple: false, apply_to: ['string'] },
            { type: 'field_between', optgroup: '', nb_inputs: 3, multiple: false, apply_to: ['string'] },
            { type: 'between_dates', optgroup: '', nb_inputs: 2, multiple: false, apply_to: ['date'] },
            { type: 'in_one_of', optgroup: '', nb_inputs: 1, multiple: true, apply_to: ['array'] },
        ]),
        lang: {
            operators: {
                field_between: '[Field Name] between [value1(number)] and [value2(number)]',
                field_greater: '[Field Name] > [value(number)]',
                field_less: '[Field Name] < [value(number)]',
                field_equal: '[Field Name] = [value]',
                field_not_equal: '[Field Name] <> [value]',
                between_dates: 'between',
                in_one_of: 'in one of'
            }
        },
        filters: [
            {
                id: 'user_group',
                label: 'User group',
                type: 'string',
                input: 'select',
                multiple: true,
                values: groups,
                plugin: 'selectpicker',
                plugin_config: {
                    liveSearch: true,
                    width: 'auto',
                    liveSearchStyle: 'contains',
                },
                operators: ['in', 'not_in', 'in_one_of']
            }, {
                id: 'field',
                label: 'Field',
                type: 'string',
                input: 'text',
                operators: ['field_equal', 'field_not_equal', 'field_greater', 'field_less', 'field_between'],
                validation: {
                    allow_empty_value: true
                }
            }, {
                id: '__current_date__',
                label: 'Current Date',
                type: 'date',
                validation: {
                    format: 'DD-MM-YYYY' // moment.js format
                },
                plugin: 'flatpickr',
                plugin_config: {
                    enableTime: false,
                    dateFormat: 'd-m-Y', // flatpickr format
                    time_24hr: true,
                    weekNumbers: true,
                    locale: {
                        firstDayOfWeek: 1
                    },
                },
                operators: ['equal', 'not_equal', 'less', 'less_or_equal', 'greater', 'greater_or_equal', 'between_dates']
            }, {
                id: '__current_date_time__',
                label: 'Current Datetime',
                type: 'date',
                validation: {
                    format: 'DD-MM-YYYY HH:mm' // moment.js format
                },
                plugin: 'flatpickr',
                plugin_config: {
                    enableTime: true,
                    dateFormat: 'd-m-Y H:i', // flatpickr format
                    time_24hr: true,
                    weekNumbers: true,
                    locale: {
                        firstDayOfWeek: 1
                    },
                },
                operators: ['less', 'less_or_equal', 'greater', 'greater_or_equal', 'between_dates']
            }, {
                id: '__current_time__',
                label: 'Current Time',
                type: 'date',
                validation: {
                    format: 'HH:mm' // moment.js format
                },
                plugin: 'flatpickr',
                plugin_config: {
                    enableTime: true,
                    dateFormat: 'H:i', // flatpickr format
                    time_24hr: true,
                    noCalendar: true,
                },
                operators: ['less', 'less_or_equal', 'greater', 'greater_or_equal', 'between_dates']
            }, {
                id: '__keyword__',
                label: 'Page Keyword',
                type: 'string',
                input: 'text',
                operators: ['equal', 'not_equal']
            }, {
                id: '__platform__',
                label: 'Platform',
                type: 'string',
                input: 'select',
                operators: ['equal'],
                values: platforms,
                plugin: 'selectpicker',
                plugin_config: {
                    liveSearch: true,
                    width: 'auto',
                    liveSearchStyle: 'contains',
                }
            }, {
                id: '__language__',
                label: 'Language',
                type: 'string',
                input: 'select',
                operators: ['equal'],
                values: languages,
                plugin: 'selectpicker',
                plugin_config: {
                    liveSearch: true,
                    width: 'auto',
                    liveSearchStyle: 'contains',
                }
            }, {
                id: '__last_login__',
                label: 'Last Login',
                type: 'string',
                input: 'text',
                operators: ['equal', 'not_equal', 'less', 'less_or_equal', 'greater', 'greater_or_equal']
            }
        ],
        // rules: rules_basic
    };

    var rules = null;

    try {
        rules = JSON.parse(jqueryBuilderJsonInput);
    } catch (error) {
        console.log('Rules cannot be parsed');
    }

    if (rules) {
        // load the rules if they exist
        queryStructure['rules'] = rules;
    }

    if ($('.condition_builder').length > 0) {
        $('.condition_builder').queryBuilder(queryStructure);
    }
}

// ********************************************* CONDITION BUILDER *****************************************

//recursive function to convert the jquery json to JSON logic
function convertRules(rules) {
    var jsonLogic = {};
    if (!rules || !rules["condition"]) {
        alert('Wrong condition!');
        return {};
    }
    jsonLogic[rules.condition] = [];
    rules.rules.forEach(rule => {
        var valuePrefix = rule.field == 'user_group' ? '$' : ''; // if the filed is user group we add the $ prefix
        // get date and time formats if fields need it
        var flatpickrMomentFormat = 'DD-MM-YYYY HH:mm';
        var momentFormat = 'YYYY-MM-DD HH:mm';
        if (rule.field == '__current_date__') {
            flatpickrMomentFormat = 'DD-MM-YYYY';
            momentFormat = 'YYYY-MM-DD';
        } else if (rule.field == '__current_time__') {
            flatpickrMomentFormat = 'HH:mm';
            momentFormat = 'HH:mm';
        }

        if (['in', 'not_in'].includes(rule.operator)) {
            rule.value.forEach(val => {
                var r = {};
                r[jsonLogicOperators[rule.operator].op] = [true, valuePrefix + val]
                jsonLogic[rules.condition].push(r);
            });
        } else if (rule.operator == 'in_one_of') {
            // add additional OR for one of these groups
            var in_one_of = {
                "or": []
            };
            rule.value.forEach(val => {
                var r = {};
                r[jsonLogicOperators[rule.operator].op] = [true, valuePrefix + val]
                in_one_of['or'].push(r);
            });
            jsonLogic[rules.condition].push(in_one_of);
        }
        else if (['field_equal', 'field_not_equal', 'field_greater', 'field_less'].includes(rule.operator)) {
            var r = {};
            r[jsonLogicOperators[rule.operator].op] = [rule.value[0], (rule.value[1] == null ? '' : rule.value[1])]
            jsonLogic[rules.condition].push(r);
        } else if (rule.operator == 'field_between') {
            // add additional AND for one of these groups
            jsonLogic[rules.condition] = {
                "and": []
            }
            jsonLogic[rules.condition]['and'].push({
                ">=": [rule.value[0], rule.value[1]]
            });
            jsonLogic[rules.condition]['and'].push({
                "<=": [rule.value[0], rule.value[2]]
            });
        } else if (rule.operator == 'between_dates') {
            // add additional AND for one of these groups            
            jsonLogic[rules.condition] = {
                "and": []
            }
            jsonLogic[rules.condition]['and'].push({
                ">=": [rule.field, new moment(rule.value[0], flatpickrMomentFormat).format(momentFormat)]
            });
            jsonLogic[rules.condition]['and'].push({
                "<=": [rule.field, new moment(rule.value[1], flatpickrMomentFormat).format(momentFormat)]
            });
        } else if (['equal', 'not_equal', 'less', 'less_or_equal', 'greater', 'greater_or_equal'].includes(rule.operator)) {
            var r = {};
            var val = rule.value;
            if (['__current_date__', '__current_date_time__', '__current_time__'].includes(rule.field)) {
                // if needed convert date
                val = new moment(rule.value, flatpickrMomentFormat).format(momentFormat);
            }
            r[jsonLogicOperators[rule.operator].op] = [rule.field, val]
            jsonLogic[rules.condition].push(r);
        }
        if (rule['rules']) {
            // recursive loop for groups
            jsonLogic[rules.condition].push(convertRules(rule));
        }
    });
    return jsonLogic;
}

// convert queryBuilder rules to JSON logic that we can use
function rulesToJsonLogic(rules) {
    if (rules) {
        rules = JSON.parse(JSON.stringify(rules).replace('"AND"', '"and"').replace('"OR"', '"or"'));
        var jsonLogic = convertRules(rules);
        jsonLogic = JSON.parse(JSON.stringify(jsonLogic).replace('"AND"', '"and"').replace('"OR"', '"or"'));
        return jsonLogic;
    } else {
        return null;
    }
}

//********************************************** FUNCTIONS *****************************************************

async function getGroups() {
    var groups = [];
    jQuery.ajax({
        url: BASE_PATH + '/request/AjaxDataSource/get_groups',
        async: false,
        cache: false,
        dataType: "json",
        success: function (data) {
            if (data.success) {
                try {
                    groups = JSON.parse(data.data);
                } catch (error) {
                    console.log('Error while parsing JSON', data.data);
                }
            }
            else {
                console.log(data);
            }
        }
    });
    return groups;
}

async function getLookups(lookupType) {
    var lookups = [];
    jQuery.ajax({
        url: BASE_PATH + '/request/AjaxDataSource/get_lookups',
        async: false,
        cache: false,
        type: 'post',
        data: { lookupType: lookupType },
        dataType: "json",
        success: function (data) {
            if (data.success) {
                try {
                    lookups = JSON.parse(data.data);
                } catch (error) {
                    console.log('Error while parsing JSON', data.data);
                }
            }
            else {
                console.log(data);
            }
        }
    });
    return lookups;
}

async function getLanguages() {
    var languages = [];
    jQuery.ajax({
        url: BASE_PATH + '/request/AjaxDataSource/get_languages',
        async: false,
        cache: false,
        dataType: "json",
        success: function (data) {
            if (data.success) {
                try {
                    languages = JSON.parse(data.data);
                } catch (error) {
                    console.log('Error while parsing JSON', data.data);
                }
            }
            else {
                console.log(data);
            }
        }
    });
    return languages;
}

//********************************************** FUNCTIONS *****************************************************

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

