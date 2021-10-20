$(document).ready(() => {
    check_radio_locked_after_submit();
});

function check_radio_locked_after_submit(){
    $('.selfhelpRadio').each(function(){
        console.log( );
        if($(this).data('locked_after_submit') && $('input[name="'+$(this).attr('name')+'"]:checked').val()){
            $('input[name="'+$(this).attr('name')+'"]:not(:checked)').prop('disabled', true);
        }        
    })
}