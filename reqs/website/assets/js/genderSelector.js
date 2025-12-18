// Gender Selector Handler
document.addEventListener('DOMContentLoaded', function () {
    const genderOptions = document.querySelectorAll('.gender-option');
    const genderInput = document.getElementById('gender');

    genderOptions.forEach(option => {
        option.addEventListener('click', function () {
            // Remove active class from all options
            genderOptions.forEach(opt => opt.classList.remove('active'));

            // Add active class to clicked option
            this.classList.add('active');

            // Set the hidden input value
            const selectedGender = this.getAttribute('data-value');
            genderInput.value = selectedGender;
        });
    });
});
