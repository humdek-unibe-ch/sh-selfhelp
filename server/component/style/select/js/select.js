$(document).ready(function () {
    initSelect();
});

function initSelect() {
    clearOptionHack();
    $('.bootstrapSelect').each(function () {
        var $select = $(this);
        var options = {
            showTick: true,
            allowClear: true
        };
        // Honour data-size explicitly so `max` from PHP always reaches selectpicker.
        var sizeAttr = $select.attr('data-size');
        if (sizeAttr !== undefined && sizeAttr !== null && sizeAttr !== '') {
            var parsedSize = parseInt(sizeAttr, 10);
            options.size = isNaN(parsedSize) ? sizeAttr : parsedSize;
        }
        $select.selectpicker(options);
    });

    // Re-apply size after open so max-height matches real rendered row height
    // (bootstrap-select may have measured before layout finished).
    $(document)
        .off('shown.bs.select.selfhelpSize')
        .on('shown.bs.select.selfhelpSize', '.bootstrapSelect', function () {
            applyBootstrapSelectMaxSize($(this));
        });

    $('.selectImage').each((index, value) => {
        initSelectImage(value);
    })

    check_select_locked_after_submit();
}

/**
 * Enforce data-size / max as N visible option rows.
 *
 * @param {jQuery} $select Underlying <select.bootstrapSelect>
 */
function applyBootstrapSelectMaxSize($select) {
    var sizeAttr = $select.attr('data-size');
    if (sizeAttr === undefined || sizeAttr === null || sizeAttr === '' || sizeAttr === 'false' || sizeAttr === 'auto') {
        return;
    }
    var size = parseInt(sizeAttr, 10);
    if (!size || size < 1) {
        return;
    }

    var picker = $select.data('selectpicker');
    if (!picker || !picker.$menuInner || !picker.$menuInner.length) {
        return;
    }

    var $li = picker.$menuInner
        .find('li:not(.dropdown-header):not(.divider):not(.hidden):not(.disabled)')
        .filter(function () {
            return $(this).css('display') !== 'none';
        })
        .first();

    if (!$li.length) {
        return;
    }

    var liHeight = $li.outerHeight(true);
    if (!liHeight) {
        return;
    }

    var innerHeight = Math.ceil(liHeight * size);
    picker.$menuInner.css('max-height', innerHeight + 'px');
    if (picker.sizeInfo) {
        picker.sizeInfo.liHeight = liHeight;
        picker.sizeInfo.menuInnerHeight = innerHeight;
    }
}

function initSelectImage(el) {
    var selectImageId = $(el).attr('id');
    var classNames = $(el).attr('class');
    var iconSelect = new IconSelect(selectImageId);
    var selectedValueHolder = document.getElementById('selectValue-' + selectImageId.split('-')[1]);

    $(el).on('changed', function (e) {
        selectedValueHolder.value = iconSelect.getSelectedValue();
    });

    var dataValues = $(el).attr('data-values');
    dataValues = JSON.parse(dataValues);

    var icons = [];
    var selectedValue = $(selectedValueHolder).val();
    dataValues.forEach(value => {
        if (value['value'] == selectedValue) {
            // push selected value first if exist, this is the default one
            icons.unshift({ 'iconFilePath': value['text'], 'iconValue': value['value'] });
        } else {
            // fill the list if not already pushed as selected value
            icons.push({ 'iconFilePath': value['text'], 'iconValue': value['value'] });
        }
    });
    iconSelect.refresh(icons);
    $(el).addClass(classNames);
}

function check_select_locked_after_submit() {
    $('.selfhelpSelect').each(function () {
        if ($(this).data('locked_after_submit') && $(this).val()) {
            $(this).find("option:not(:selected)").prop('disabled', true);
            $(this).selectpicker('refresh');
        }
    })
}

function clearOptionHack() {
    $('.bootstrapSelect').each(function () {
        if ($(this).val()) {
            setClearButton(this);
        }
    });
    $('.bootstrapSelect').on('changed.bs.select', function () {
        setClearButton(this);
    });
}

function setClearButton(button) {
    let self = button,
        clearClass = `clear`,
        appendDom = `<span>&times;</span>`,
        noCaretClass = `dropdown-toggle-no-carret`,
        $bselect = $(self).parents(`.bootstrap-select`),
        $dropdown = $bselect.find(`[data-toggle="dropdown"]`);

    if ($(self).data("allow-clear") !== true)
        return false;
    if ($dropdown.find(`.${clearClass}`).length == 0) {
        $dropdown.addClass(noCaretClass)
            .append(
                $(appendDom)
                    .attr({
                        'class': clearClass,
                        'aria-hidden': true
                    })
                    .css({
                        'font-size': '20px',
                        'line-height': '20px',
                        'margin-top': '-6px'
                    })
                    .on('click', function (e) {
                        e.stopPropagation();
                        $dropdown.removeClass(noCaretClass)
                        $(self).val('').selectpicker('refresh');
                        if (typeof unsavedChanges !== 'undefined'){
                            unsavedChanges.push(self);
                        }
                        $(this).remove();
                    })
            )
    }
}
