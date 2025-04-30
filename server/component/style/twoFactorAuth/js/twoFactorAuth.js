$(document).ready(() => {
    init_2fa_inputs();
    init_2fa_timer();
});

function init_2fa_inputs() {
    const inputs = $('.selfhelp-2fa-input');
    const form = $('#selfhelp-2fa-form');

    // Handle paste event on the first input
    $(inputs[0]).on('paste', function(e) {
        e.preventDefault();
        const pastedData = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
        const digits = pastedData.replace(/\D/g, '').split('').slice(0, 6);
        
        digits.forEach((digit, index) => {
            if (index < inputs.length) {
                $(inputs[index]).val(digit);
                if (index === digits.length - 1 && digits.length === 6) {
                    form.submit();
                }
            }
        });
    });

    inputs.each(function(index) {
        // Auto-focus and auto-tab functionality
        $(this).on('input', function() {
            if (this.value.length === 1) {
                if (index < inputs.length - 1) {
                    $(inputs[index + 1]).focus();
                } else if (index === inputs.length - 1) {
                    // If all inputs are filled, submit the form
                    let allFilled = true;
                    inputs.each(function() {
                        if (!this.value) allFilled = false;
                    });
                    if (allFilled) form.submit();
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
