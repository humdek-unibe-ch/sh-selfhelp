$(document).ready(function() {
    autosize($('textarea'));
    check_textarea_locked_after_submit();
});

function check_textarea_locked_after_submit(){
    $('.selfhelpTextArea').each(function(){
        if($(this).data('locked_after_submit') && $(this).val()){
            $(this).prop('readonly', true);
        }        
    })
}
