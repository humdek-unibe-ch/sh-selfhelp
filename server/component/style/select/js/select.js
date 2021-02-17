$(document).ready(function () {
    $('.bootstrapSelect').selectpicker({
        showTick: true,
        allowClear: true
    });

    $('.selectImage').each((index, value) => {
        initSelectImage(value);
    })

});

function initSelectImage(el) {
    var selectImageId = $(el).attr('id');
    var classNames = $(el).attr('class');
    iconSelect = new IconSelect(selectImageId);
    selectedValueHolder = document.getElementById('selectValue-' + selectImageId.split('-')[1]);

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