$(document).ready(() => {
    init_2fa_inputs();
    init_2fa_timer();
});

function init_2fa_inputs() {
    const inputs = $('.selfhelp-2fa-input');
    inputs.each(function(index) {
        // Auto-focus and auto-tab functionality
        $(this).on('input', function() {
            if (this.value.length === 1) {
                if (index < inputs.length - 1) {
                    $(inputs[index + 1]).focus();
                }
            }
        });

        // Handle backspace
        $(this).on('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && index > 0) {
                $(inputs[index - 1]).focus();
            }
        });

        // Ensure only numbers
        $(this).on('keypress', function(e) {
            if (e.which < 48 || e.which > 57) {
                e.preventDefault();
            }
        });
    });
}

function init_2fa_timer() {
    const timerElement = $('#selfhelp-2fa-timer');
    if (!timerElement.length) return;

    let timeLeft = parseInt(timerElement.data('time-remaining'), 10);
    
    function updateTimer() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerElement.text(`${minutes}:${seconds.toString().padStart(2, '0')}`);
        
        if (timeLeft > 0) {
            timeLeft--;
            setTimeout(updateTimer, 1000);
        } else {
            timerElement.text('Expired');
        }
    }
    
    updateTimer();
}
