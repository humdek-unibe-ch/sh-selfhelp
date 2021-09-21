$(document).ready(() => {
    check_locked_after_submit();
});

function check_locked_after_submit(){
    $('.selfhelpRadio').each(function(){
        if($(this).data('locked_after_submit') && $('input[name="'+$(this).attr('name')+'"]:checked').val()){
            $(this).prop('disabled', true);
        }        
    })
}