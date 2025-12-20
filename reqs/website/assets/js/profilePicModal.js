document.addEventListener('DOMContentLoaded', () => {
    const openModalBtn = document.getElementById('open-profile-modal');
    const closeModalBtn = document.querySelector('.close-modal');
    const modal = document.getElementById('profile-pic-modal');
    const modalOverlay = document.getElementById('modal-overlay');
    const ilstrImgBtn = document.getElementById('ilstr-img');
    const dfltImgBtn = document.getElementById('dflt-img');
    const uploadDiv = document.querySelector('.upload');
    const defaultDiv = document.querySelector('.default');
    const dropArea = document.querySelector('.drop-area');
    const fileInput = document.getElementById('profile-pic-upload');
    const hiddenInput = document.getElementById('default-picture');
    const currentAvatar = document.getElementById('current-avatar');

    // Function to close the modal
    const closeModal = () => {
        modal.style.display = 'none';
        modalOverlay.style.display = 'none';
    };

    ilstrImgBtn.classList.add('active');
    uploadDiv.style.display = 'none';
    defaultDiv.style.display = 'block';

    openModalBtn.addEventListener('click', () => {
        modal.style.display = 'block';
        modalOverlay.style.display = 'block';
    });

    closeModalBtn.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', closeModal);

    ilstrImgBtn.addEventListener('click', () => {
        uploadDiv.style.display = 'none';
        defaultDiv.style.display = 'block';
        ilstrImgBtn.classList.add('active');
        dfltImgBtn.classList.remove('active');
        fileInput.value = '';
    });

    dfltImgBtn.addEventListener('click', () => {
        uploadDiv.style.display = 'block';
        defaultDiv.style.display = 'none';
        dfltImgBtn.classList.add('active');
        ilstrImgBtn.classList.remove('active');
        hiddenInput.value = '';
        document.querySelectorAll('.def-avatars').forEach(img => {
            img.classList.remove('active');
        });
    });

    dropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropArea.classList.add('drag-over');
    });

    dropArea.addEventListener('dragleave', () => {
        dropArea.classList.remove('drag-over');
    });

    dropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dropArea.classList.remove('drag-over');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileUpload(files[0]);
        }
    });

    dropArea.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files && e.target.files[0]) {
            handleFileUpload(e.target.files[0]);
        }
    });

    const defaultImages = document.querySelectorAll('.default-grid img');
    defaultImages.forEach(image => {
        image.addEventListener('click', function() {
            defaultImages.forEach(img => img.classList.remove('active'));
            this.classList.add('active');
            
            hiddenInput.value = this.getAttribute('data-value');
            currentAvatar.src = this.src;
            fileInput.value = '';
            
            // Close modal immediately after selection
            closeModal();
        });
    });

    function handleFileUpload(file) {
        hiddenInput.value = '';
        defaultImages.forEach(img => img.classList.remove('active'));
        
        // File validation will be handled by backend
        const reader = new FileReader();
        reader.onload = function(e) {
            currentAvatar.src = e.target.result;
            // Close modal immediately after file is loaded
            closeModal();
        };
        reader.readAsDataURL(file);
    }
});
