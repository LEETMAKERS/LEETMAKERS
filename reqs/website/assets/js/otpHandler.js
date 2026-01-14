document.addEventListener("DOMContentLoaded", () => {
    const otpInputs = document.querySelectorAll('.otp-input');

    otpInputs.forEach((input, index) => {
        input.addEventListener('input', (event) => {
            const currentInput = event.target;
            currentInput.value = currentInput.value.replace(/[^0-9]/g, '').slice(0, 1); // Allow only one digit

            // Add or remove 'filled' class based on the input value
            if (currentInput.value.length === 1) {
                currentInput.classList.add('filled');
                // Move to the next input if the current one is filled
                if (index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            } else {
                currentInput.classList.remove('filled');
            }
        });

        input.addEventListener('keydown', (event) => {
            // Allow backspace to move to the previous input
            if (event.key === 'Backspace' && input.value === '' && index > 0) {
                otpInputs[index - 1].focus();
            }
        });

        // Handle paste event to distribute OTP across all inputs
        input.addEventListener('paste', (event) => {
            event.preventDefault();
            const pastedData = (event.clipboardData || window.clipboardData).getData('text');
            const digits = pastedData.replace(/[^0-9]/g, '').split('').slice(0, otpInputs.length);

            digits.forEach((digit, i) => {
                if (otpInputs[i]) {
                    otpInputs[i].value = digit;
                    otpInputs[i].classList.add('filled');
                }
            });

            // Focus the next empty input or the last one
            const nextEmptyIndex = digits.length < otpInputs.length ? digits.length : otpInputs.length - 1;
            otpInputs[nextEmptyIndex].focus();
        });
    });
});
