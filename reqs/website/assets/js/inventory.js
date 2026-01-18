
(function () {
    'use strict';

    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function () {
        // Check for stored notification from previous page (before reload)
        checkStoredNotification();

        const table = document.querySelector('.inventory-table');
        if (!table) return;

        // Check if table is empty and show/hide empty state
        checkTableEmpty();

        // Initialize sorting
        initTableSorting(table);

        // Initialize admin modals (if admin)
        initAdminModals();
    });

    /**
     * Check for stored notification from previous action
     */
    function checkStoredNotification() {
        const storedNotification = sessionStorage.getItem('inventoryNotification');
        if (storedNotification) {
            const { type, message } = JSON.parse(storedNotification);
            sessionStorage.removeItem('inventoryNotification');
            // Slight delay to ensure page is ready
            setTimeout(() => {
                showNotification(type, message);
            }, 100);
        }
    }

    /**
     * Store notification for display after page reload
     */
    function storeNotification(type, message) {
        sessionStorage.setItem('inventoryNotification', JSON.stringify({ type, message }));
    }

    /**
     * Initialize table sorting functionality
     */
    function initTableSorting(table) {
        const headers = table.querySelectorAll('th .th-content');
        let currentSortColumn = null;
        let currentSortOrder = 'asc';

        headers.forEach((header, index) => {
            const sortIcon = header.querySelector('.fa-angle-down');
            if (!sortIcon) return;

            header.addEventListener('click', () => {
                sortTable(table, index, header, headers);
            });
        });

        function sortTable(table, columnIndex, headerElement, allHeaders) {
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            if (currentSortColumn === columnIndex) {
                currentSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                currentSortOrder = 'asc';
            }

            currentSortColumn = columnIndex;

            allHeaders.forEach(h => {
                h.classList.remove('sorted', 'sorted-asc', 'sorted-desc');
            });

            headerElement.classList.add('sorted');
            headerElement.classList.add(currentSortOrder === 'asc' ? 'sorted-asc' : 'sorted-desc');

            rows.sort((a, b) => {
                const aCell = a.querySelectorAll('td')[columnIndex];
                const bCell = b.querySelectorAll('td')[columnIndex];

                let aValue = getCellValue(aCell, columnIndex);
                let bValue = getCellValue(bCell, columnIndex);

                // ID or Quantity columns (numeric)
                if (columnIndex === 0 || columnIndex === 4) {
                    aValue = parseInt(aValue) || 0;
                    bValue = parseInt(bValue) || 0;
                    return currentSortOrder === 'asc' ? aValue - bValue : bValue - aValue;
                } else {
                    aValue = aValue.toLowerCase();
                    bValue = bValue.toLowerCase();
                    if (currentSortOrder === 'asc') {
                        return aValue.localeCompare(bValue);
                    } else {
                        return bValue.localeCompare(aValue);
                    }
                }
            });

            tbody.innerHTML = '';
            rows.forEach((row, index) => {
                row.style.animation = 'fadeInRow 0.3s ease forwards';
                row.style.animationDelay = `${index * 0.02}s`;
                tbody.appendChild(row);
            });
        }
    }

    function getCellValue(cell, columnIndex) {
        if (columnIndex === 0) {
            return cell.textContent.trim();
        } else if (columnIndex === 1) {
            const itemName = cell.querySelector('.item-name span');
            return itemName ? itemName.textContent.trim() : cell.textContent.trim();
        } else if (columnIndex === 3) {
            return cell.textContent.trim();
        } else if (columnIndex === 4) {
            const quantityBadge = cell.querySelector('.quantity-badge');
            return quantityBadge ? quantityBadge.textContent.trim() : cell.textContent.trim();
        }
        return cell.textContent.trim();
    }

    function checkTableEmpty() {
        const table = document.querySelector('.inventory-table');
        if (!table) return;

        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        const emptyState = document.querySelector('.empty-state');

        if (rows.length === 0) {
            if (emptyState) emptyState.style.display = 'flex';
        } else {
            if (emptyState) emptyState.style.display = 'none';
        }
    }

    /**
     * Initialize admin modal functionality
     */
    function initAdminModals() {
        const addItemBtn = document.getElementById('add-item-btn');
        const addItemModal = document.getElementById('add-item-modal');
        const editItemModal = document.getElementById('edit-item-modal');
        const deleteItemModal = document.getElementById('delete-item-modal');
        const modalOverlay = document.getElementById('modal-overlay');

        // Exit if not admin (modals don't exist)
        if (!addItemModal) return;

        // Load default images when page loads
        loadDefaultImages();

        // Initialize export dropdown
        initExportDropdown();

        // Initialize import dropdown and modal
        initImportDropdown();
        initImportModal();

        // Initialize clear inventory
        initClearInventory();

        // Open Add Item Modal
        if (addItemBtn) {
            addItemBtn.addEventListener('click', () => {
                openModal(addItemModal);
            });
        }

        // Close modals
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                const modalId = btn.getAttribute('data-modal');
                const modal = document.getElementById(modalId);
                if (modal) closeModal(modal);
            });
        });

        document.querySelectorAll('.btn-cancel').forEach(btn => {
            btn.addEventListener('click', () => {
                const modalId = btn.getAttribute('data-modal');
                const modal = document.getElementById(modalId);
                if (modal) closeModal(modal);
            });
        });

        // Close on overlay click
        if (modalOverlay) {
            modalOverlay.addEventListener('click', closeAllModals);
        }

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeAllModals();
            }
        });

        // Image selection tabs
        initImageTabs();

        // Edit modal image tabs
        initEditImageTabs();

        // Form submissions
        initFormSubmissions();

        // Edit and Delete button handlers
        initActionButtons();
    }

    /**
     * Open a modal
     */
    function openModal(modal) {
        const overlay = document.getElementById('modal-overlay');
        if (modal) modal.style.display = 'block';
        if (overlay) overlay.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    /**
     * Close a modal
     */
    function closeModal(modal) {
        const overlay = document.getElementById('modal-overlay');
        if (modal) modal.style.display = 'none';
        if (overlay) overlay.style.display = 'none';
        document.body.style.overflow = '';
    }

    /**
     * Close all modals
     */
    function closeAllModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
        });
        const overlay = document.getElementById('modal-overlay');
        if (overlay) overlay.style.display = 'none';
        document.body.style.overflow = '';
    }

    /**
     * Initialize export dropdown functionality
     */
    function initExportDropdown() {
        const dropdown = document.getElementById('export-dropdown');
        const exportBtn = document.getElementById('export-btn');
        const exportOptions = document.querySelectorAll('.export-option');

        if (!dropdown || !exportBtn) return;

        // Toggle dropdown on button click
        exportBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('open');
        });

        // Handle export option clicks
        exportOptions.forEach(option => {
            option.addEventListener('click', (e) => {
                e.preventDefault();
                const format = option.getAttribute('data-format');
                dropdown.classList.remove('open');
                exportInventory(format);
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });
    }

    /**
     * Export inventory data
     */
    function exportInventory(format) {
        // Create a temporary link to trigger download
        const url = `/utils/manageInventory?action=export&format=${format}`;
        const link = document.createElement('a');
        link.href = url;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showNotification('success', `Success: Inventory exported as ${format.toUpperCase()}`);
    }

    /**
     * Initialize import dropdown functionality
     */
    function initImportDropdown() {
        const dropdown = document.getElementById('import-dropdown');
        const importBtn = document.getElementById('import-btn');
        const importModal = document.getElementById('import-item-modal');
        const importOptions = document.querySelectorAll('.import-option');

        if (!dropdown || !importBtn) return;

        // Toggle dropdown on button click
        importBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('open');
            // Close export dropdown if open
            const exportDropdown = document.getElementById('export-dropdown');
            if (exportDropdown) exportDropdown.classList.remove('open');
        });

        // Handle import option clicks - open modal with format hint
        importOptions.forEach(option => {
            option.addEventListener('click', (e) => {
                e.preventDefault();
                const format = option.getAttribute('data-format');
                dropdown.classList.remove('open');

                // Switch to the correct format tab
                const formatTabs = document.querySelectorAll('.format-tab');
                formatTabs.forEach(tab => {
                    if (tab.getAttribute('data-format') === format) {
                        tab.click();
                    }
                });

                // Open import modal
                if (importModal) openModal(importModal);
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });
    }

    /**
     * Initialize import modal functionality
     */
    function initImportModal() {
        const formatTabs = document.querySelectorAll('.format-tab');
        const csvExample = document.getElementById('format-example-csv');
        const jsonExample = document.getElementById('format-example-json');
        const dropArea = document.getElementById('import-drop-area');
        const fileInput = document.getElementById('import-file-input');
        const filePreview = document.getElementById('import-file-preview');
        const fileName = document.getElementById('import-file-name');
        const fileSize = document.getElementById('import-file-size');
        const fileIcon = document.getElementById('import-file-icon');
        const removeFileBtn = document.getElementById('remove-import-file');
        const submitBtn = document.querySelector('.btn-import-submit');

        if (!dropArea || !fileInput) return;

        // Format tabs switching
        formatTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                formatTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                const format = tab.getAttribute('data-format');
                if (format === 'csv') {
                    if (csvExample) csvExample.style.display = 'block';
                    if (jsonExample) jsonExample.style.display = 'none';
                } else {
                    if (csvExample) csvExample.style.display = 'none';
                    if (jsonExample) jsonExample.style.display = 'block';
                }
            });
        });

        // Click to select file
        dropArea.addEventListener('click', () => {
            fileInput.click();
        });

        // Drag events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => {
                dropArea.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => {
                dropArea.classList.remove('dragover');
            });
        });

        // Handle drop
        dropArea.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleImportFileSelect(files[0]);
            }
        });

        // Handle file input change
        fileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) {
                handleImportFileSelect(e.target.files[0]);
            }
        });

        // Remove file
        if (removeFileBtn) {
            removeFileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                clearImportFile();
            });
        }

        function handleImportFileSelect(file) {
            // Validate file type
            const validExtensions = ['.csv', '.json'];
            const extension = '.' + file.name.split('.').pop().toLowerCase();

            if (!validExtensions.includes(extension)) {
                showNotification('error', 'Error: Only CSV and JSON files are supported');
                return;
            }

            // Update file input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;

            // Show preview
            if (fileName) fileName.textContent = file.name;
            if (fileSize) fileSize.textContent = formatFileSize(file.size);
            if (fileIcon) {
                fileIcon.className = extension === '.json' ? 'ri-code-s-slash-line' : 'ri-file-text-line';
            }
            if (dropArea) dropArea.style.display = 'none';
            if (filePreview) filePreview.style.display = 'flex';
            if (submitBtn) submitBtn.disabled = false;

            // Switch to matching format tab
            const format = extension === '.json' ? 'json' : 'csv';
            formatTabs.forEach(tab => {
                if (tab.getAttribute('data-format') === format) {
                    tab.click();
                }
            });
        }

        function clearImportFile() {
            fileInput.value = '';
            if (dropArea) dropArea.style.display = 'block';
            if (filePreview) filePreview.style.display = 'none';
            if (submitBtn) submitBtn.disabled = true;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Reset import modal when closed
        const importModal = document.getElementById('import-item-modal');
        if (importModal) {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'style') {
                        const display = window.getComputedStyle(importModal).display;
                        if (display === 'none') {
                            clearImportFile();
                        }
                    }
                });
            });
            observer.observe(importModal, { attributes: true });
        }
    }

    /**
     * Handle import form submission
     */
    function handleImportItems(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const fileInput = document.getElementById('import-file-input');

        if (!fileInput.files || fileInput.files.length === 0) {
            showNotification('error', 'Error: Please select a file to import');
            return;
        }

        // Show loading state
        const submitBtn = form.querySelector('.btn-import-submit');
        const originalHTML = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="ri-loader-4-line"></i> Importing...';
        submitBtn.disabled = true;

        fetch('/utils/manageInventory', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    storeNotification('success', data.message);
                    closeAllModals();
                    window.location.reload();
                } else {
                    showNotification('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Error: An error occurred while importing');
            })
            .finally(() => {
                submitBtn.innerHTML = originalHTML;
                submitBtn.disabled = false;
            });
    }

    /**
     * Initialize clear inventory button and modal
     */
    function initClearInventory() {
        const clearBtn = document.getElementById('clear-inventory-btn');
        const clearModal = document.getElementById('clear-inventory-modal');
        const passwordInput = document.getElementById('clear-password');
        const togglePasswordBtn = clearModal?.querySelector('.toggle-password');

        if (!clearBtn || !clearModal) return;

        // Open clear modal
        clearBtn.addEventListener('click', () => {
            openModal(clearModal);
            // Focus password input after modal opens
            setTimeout(() => {
                if (passwordInput) passwordInput.focus();
            }, 100);
        });

        // Toggle password visibility
        if (togglePasswordBtn && passwordInput) {
            togglePasswordBtn.addEventListener('click', () => {
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
                const icon = togglePasswordBtn.querySelector('i');
                icon.className = type === 'password' ? 'ri-eye-line' : 'ri-eye-off-line';
            });
        }

        // Reset form when modal closes
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'style') {
                    const display = window.getComputedStyle(clearModal).display;
                    if (display === 'none') {
                        const form = document.getElementById('clear-inventory-form');
                        if (form) form.reset();
                        if (passwordInput) passwordInput.type = 'password';
                        const icon = togglePasswordBtn?.querySelector('i');
                        if (icon) icon.className = 'ri-eye-line';
                    }
                }
            });
        });
        observer.observe(clearModal, { attributes: true });
    }

    /**
     * Handle clear inventory form submission
     */
    function handleClearInventory(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const passwordInput = document.getElementById('clear-password');

        if (!passwordInput.value.trim()) {
            showNotification('error', 'Error: Please enter your password');
            passwordInput.focus();
            return;
        }

        // Show loading state
        const submitBtn = form.querySelector('.btn-clear-confirm');
        const originalHTML = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="ri-loader-4-line"></i> Clearing...';
        submitBtn.disabled = true;

        fetch('/utils/manageInventory', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    storeNotification('success', data.message);
                    closeAllModals();
                    window.location.reload();
                } else {
                    showNotification('error', data.message);
                    passwordInput.value = '';
                    passwordInput.focus();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Error: An error occurred while clearing inventory');
            })
            .finally(() => {
                submitBtn.innerHTML = originalHTML;
                submitBtn.disabled = false;
            });
    }

    /**
     * Load default material images
     */
    function loadDefaultImages() {
        const grid = document.getElementById('default-images-grid');
        if (!grid) return;

        fetch('/utils/manageInventory?action=getDefaultImages')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.images) {
                    grid.innerHTML = '';
                    data.images.forEach(image => {
                        // Create container div for image and label
                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'image-item';
                        itemDiv.setAttribute('data-value', image.name);

                        // Create image element
                        const img = document.createElement('img');
                        img.src = image.path;
                        img.alt = image.displayName;
                        img.title = image.displayName;

                        // Create label element
                        const label = document.createElement('span');
                        label.className = 'image-label';
                        label.textContent = image.displayName;
                        label.title = image.displayName; // Full name on hover

                        itemDiv.appendChild(img);
                        itemDiv.appendChild(label);

                        itemDiv.addEventListener('click', function () {
                            // Remove selected class from all items
                            grid.querySelectorAll('.image-item').forEach(i => i.classList.remove('selected'));
                            // Add selected class to clicked item
                            this.classList.add('selected');
                            // Set hidden input value
                            document.getElementById('selected-default-image').value = image.name;
                            document.getElementById('image-type').value = 'default';
                            // Clear file input
                            const fileInput = document.getElementById('item-image-upload');
                            if (fileInput) fileInput.value = '';
                            // Hide upload preview
                            const preview = document.getElementById('upload-preview');
                            if (preview) preview.style.display = 'none';
                        });

                        grid.appendChild(itemDiv);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading default images:', error);
            });
    }

    /**
     * Initialize image selection tabs
     */
    function initImageTabs() {
        const defaultTab = document.getElementById('default-img-tab');
        const uploadTab = document.getElementById('upload-img-tab');
        const defaultOption = document.querySelector('.default-images');
        const uploadOption = document.querySelector('.upload-image');

        if (!defaultTab || !uploadTab) return;

        defaultTab.addEventListener('click', () => {
            defaultTab.classList.add('active');
            uploadTab.classList.remove('active');
            if (defaultOption) defaultOption.classList.add('active');
            if (uploadOption) uploadOption.classList.remove('active');
            document.getElementById('image-type').value = 'default';
        });

        uploadTab.addEventListener('click', () => {
            uploadTab.classList.add('active');
            defaultTab.classList.remove('active');
            if (uploadOption) uploadOption.classList.add('active');
            if (defaultOption) defaultOption.classList.remove('active');
            document.getElementById('image-type').value = 'upload';
        });

        // Initialize drag and drop
        initDragAndDrop();
    }

    /**
     * Initialize drag and drop for image upload
     */
    function initDragAndDrop() {
        const dropArea = document.getElementById('item-drop-area');
        const fileInput = document.getElementById('item-image-upload');
        const preview = document.getElementById('upload-preview');
        const previewImage = document.getElementById('preview-image');
        const removeBtn = document.getElementById('remove-preview');

        if (!dropArea || !fileInput) return;

        // Click to select file
        dropArea.addEventListener('click', () => {
            fileInput.click();
        });

        // Drag events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => {
                dropArea.classList.add('drag-over');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => {
                dropArea.classList.remove('drag-over');
            });
        });

        // Handle drop
        dropArea.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files[0]);
            }
        });

        // Handle file input change
        fileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) {
                handleFileSelect(e.target.files[0]);
            }
        });

        // Remove preview
        if (removeBtn) {
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                fileInput.value = '';
                if (preview) preview.style.display = 'none';
                if (dropArea) dropArea.style.display = 'flex';
            });
        }

        function handleFileSelect(file) {
            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                showNotification('error', 'Error: Invalid file type. Allowed: JPG, PNG, GIF, WEBP');
                return;
            }

            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                showNotification('error', 'Error: File size exceeds 2MB limit');
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = (e) => {
                if (previewImage) previewImage.src = e.target.result;
                if (preview) preview.style.display = 'flex';
                if (dropArea) dropArea.style.display = 'none';
            };
            reader.readAsDataURL(file);

            // Clear default image selection
            document.getElementById('selected-default-image').value = '';
            document.querySelectorAll('#default-images-grid img').forEach(img => {
                img.classList.remove('selected');
            });
        }
    }

    /**
     * Initialize form submissions
     */
    function initFormSubmissions() {
        // Add Item Form
        const addForm = document.getElementById('add-item-form');
        if (addForm) {
            addForm.addEventListener('submit', handleAddItem);
        }

        // Edit Item Form
        const editForm = document.getElementById('edit-item-form');
        if (editForm) {
            editForm.addEventListener('submit', handleEditItem);
        }

        // Delete Item Form
        const deleteForm = document.getElementById('delete-item-form');
        if (deleteForm) {
            deleteForm.addEventListener('submit', handleDeleteItem);
        }

        // Merge Item Form
        const mergeForm = document.getElementById('merge-item-form');
        if (mergeForm) {
            mergeForm.addEventListener('submit', handleMergeItem);
        }

        // Import Item Form
        const importForm = document.getElementById('import-item-form');
        if (importForm) {
            importForm.addEventListener('submit', handleImportItems);
        }

        // Clear Inventory Form
        const clearForm = document.getElementById('clear-inventory-form');
        if (clearForm) {
            clearForm.addEventListener('submit', handleClearInventory);
        }
    }

    /**
     * Handle Add Item form submission
     */
    function handleAddItem(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);

        // Show loading state
        const submitBtn = form.querySelector('.btn-confirm');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Adding...';
        submitBtn.disabled = true;

        fetch('/utils/manageInventory', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    storeNotification('success', data.message);
                    closeAllModals();
                    window.location.reload();
                } else {
                    showNotification('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Error: An error occurred while adding the item');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
    }

    /**
     * Handle Edit Item form submission
     */
    function handleEditItem(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);

        const submitBtn = form.querySelector('.btn-confirm');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Updating...';
        submitBtn.disabled = true;

        fetch('/utils/manageInventory', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    storeNotification('success', data.message);
                    closeAllModals();
                    window.location.reload();
                } else if (data.conflict) {
                    // Show merge confirmation modal
                    closeModal(document.getElementById('edit-item-modal'));
                    showMergeConfirmation(data);
                } else {
                    showNotification('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Error: An error occurred while updating the item');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
    }

    /**
     * Show merge confirmation modal
     */
    function showMergeConfirmation(data) {
        const modal = document.getElementById('merge-item-modal');
        if (!modal) return;

        // Populate modal fields
        document.getElementById('merge-source-id').value = data.sourceItem.id;
        document.getElementById('merge-target-id').value = data.targetItem.id;
        document.getElementById('merge-description').textContent = data.message;
        document.getElementById('merge-source-name').textContent = data.sourceItem.item_name;
        document.getElementById('merge-source-qty').textContent = data.sourceItem.quantity;
        document.getElementById('merge-target-name').textContent = data.targetItem.item_name;
        document.getElementById('merge-target-qty').textContent = data.targetItem.quantity;
        document.getElementById('merge-combined-qty').textContent = data.combinedQuantity;

        openModal(modal);
    }

    /**
     * Handle Merge Item form submission
     */
    function handleMergeItem(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);

        const submitBtn = form.querySelector('.btn-merge');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="ri-loader-4-line"></i> Merging...';
        submitBtn.disabled = true;

        fetch('/utils/manageInventory', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    storeNotification('success', data.message);
                    closeAllModals();
                    window.location.reload();
                } else {
                    showNotification('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Error: An error occurred while merging items');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    }

    /**
     * Handle Delete Item form submission
     */
    function handleDeleteItem(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);

        const submitBtn = form.querySelector('.btn-danger');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="ri-loader-4-line"></i> Deleting...';
        submitBtn.disabled = true;

        fetch('/utils/manageInventory', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    storeNotification('success', data.message);
                    closeAllModals();
                    window.location.reload();
                } else {
                    showNotification('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Error: An error occurred while deleting the item');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    }

    /**
     * Initialize Edit and Delete action buttons
     */
    function initActionButtons() {
        // Edit buttons
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function () {
                const itemId = this.getAttribute('data-id');
                const itemName = this.getAttribute('data-name');
                const itemCategory = this.getAttribute('data-category');
                const itemImage = this.getAttribute('data-image');
                const quantity = this.getAttribute('data-quantity');

                document.getElementById('edit-item-id').value = itemId;
                document.getElementById('edit-item-name').value = itemName;
                document.getElementById('edit-item-category').value = itemCategory;
                document.getElementById('edit-quantity').value = quantity;

                // Set current image preview
                const currentImagePreview = document.getElementById('edit-current-image-preview');
                if (currentImagePreview) {
                    currentImagePreview.src = itemImage || '/assets/res/material/no-image.webp';
                }

                // Reset to "Keep Current" tab
                resetEditImageTabs();

                // Load default images for edit modal
                loadEditDefaultImages();

                openModal(document.getElementById('edit-item-modal'));
            });
        });

        // Delete buttons
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function () {
                const itemId = this.getAttribute('data-id');
                const itemName = this.getAttribute('data-name');

                document.getElementById('delete-item-id').value = itemId;
                document.getElementById('delete-item-name').textContent = itemName;

                openModal(document.getElementById('delete-item-modal'));
            });
        });
    }

    /**
     * Update table row after edit
     */
    function updateTableRow(itemId, newQuantity) {
        const row = document.querySelector(`tr[data-id="${itemId}"]`);
        if (!row) return;

        const quantityCell = row.querySelector('.quantity-badge');
        if (quantityCell) {
            quantityCell.textContent = newQuantity;

            // Update quantity badge class
            quantityCell.classList.remove('quantity-low', 'quantity-warning');
            if (parseInt(newQuantity) === 0) {
                quantityCell.classList.add('quantity-low');
            } else if (parseInt(newQuantity) <= 5) {
                quantityCell.classList.add('quantity-warning');
            }
        }

        // Update edit button data attribute
        const editBtn = row.querySelector('.btn-edit');
        if (editBtn) {
            editBtn.setAttribute('data-quantity', newQuantity);
        }

        // Update stats (refresh page is simpler for accurate stats)
        updateStats();
    }

    /**
     * Update inventory stats
     */
    function updateStats() {
        // For simplicity, just reload after a successful operation
        // In a more complex app, you'd calculate stats from DOM
    }

    /**
     * Reset edit modal image tabs to default state
     */
    function resetEditImageTabs() {
        const defaultTab = document.getElementById('edit-default-img-tab');
        const uploadTab = document.getElementById('edit-upload-img-tab');
        const defaultOption = document.querySelector('.edit-default-images');
        const uploadOption = document.querySelector('.edit-upload-image');

        // Reset tabs - default images tab is active by default
        if (defaultTab) defaultTab.classList.add('active');
        if (uploadTab) uploadTab.classList.remove('active');

        // Reset options
        if (defaultOption) defaultOption.classList.add('active');
        if (uploadOption) uploadOption.classList.remove('active');

        // Reset hidden inputs - keep by default (no image selected)
        document.getElementById('edit-image-type').value = 'keep';
        document.getElementById('edit-selected-default-image').value = '';

        // Clear any selected default images
        const grid = document.getElementById('edit-default-images-grid');
        if (grid) {
            grid.querySelectorAll('.image-item').forEach(i => i.classList.remove('selected'));
        }

        // Reset file input and preview
        const fileInput = document.getElementById('edit-item-image-upload');
        const preview = document.getElementById('edit-upload-preview');
        const dropArea = document.getElementById('edit-item-drop-area');
        if (fileInput) fileInput.value = '';
        if (preview) preview.style.display = 'none';
        if (dropArea) dropArea.style.display = 'flex';
    }

    /**
     * Load default images for edit modal
     */
    function loadEditDefaultImages() {
        const grid = document.getElementById('edit-default-images-grid');
        if (!grid) return;

        fetch('/utils/manageInventory?action=getDefaultImages')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.images) {
                    grid.innerHTML = '';
                    data.images.forEach(image => {
                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'image-item';
                        itemDiv.setAttribute('data-value', image.name);

                        const img = document.createElement('img');
                        img.src = image.path;
                        img.alt = image.displayName;
                        img.title = image.displayName;

                        const label = document.createElement('span');
                        label.className = 'image-label';
                        label.textContent = image.displayName;
                        label.title = image.displayName;

                        itemDiv.appendChild(img);
                        itemDiv.appendChild(label);

                        itemDiv.addEventListener('click', function () {
                            grid.querySelectorAll('.image-item').forEach(i => i.classList.remove('selected'));
                            this.classList.add('selected');
                            document.getElementById('edit-selected-default-image').value = image.name;
                            document.getElementById('edit-image-type').value = 'default';
                            const fileInput = document.getElementById('edit-item-image-upload');
                            if (fileInput) fileInput.value = '';
                            const preview = document.getElementById('edit-upload-preview');
                            if (preview) preview.style.display = 'none';
                        });

                        grid.appendChild(itemDiv);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading default images:', error);
            });
    }

    /**
     * Initialize edit modal image tabs
     */
    function initEditImageTabs() {
        const defaultTab = document.getElementById('edit-default-img-tab');
        const uploadTab = document.getElementById('edit-upload-img-tab');
        const defaultOption = document.querySelector('.edit-default-images');
        const uploadOption = document.querySelector('.edit-upload-image');

        if (!defaultTab || !uploadTab) return;

        defaultTab.addEventListener('click', () => {
            defaultTab.classList.add('active');
            uploadTab.classList.remove('active');
            if (defaultOption) defaultOption.classList.add('active');
            if (uploadOption) uploadOption.classList.remove('active');
            // Only set to 'default' if an image is actually selected
            // Otherwise keep as 'keep'
        });

        uploadTab.addEventListener('click', () => {
            uploadTab.classList.add('active');
            defaultTab.classList.remove('active');
            if (uploadOption) uploadOption.classList.add('active');
            if (defaultOption) defaultOption.classList.remove('active');
            // Only set to 'upload' if a file is actually selected
            // Otherwise keep as 'keep'
        });

        // Initialize drag and drop for edit modal
        initEditDragAndDrop();
    }

    /**
     * Initialize drag and drop for edit modal image upload
     */
    function initEditDragAndDrop() {
        const dropArea = document.getElementById('edit-item-drop-area');
        const fileInput = document.getElementById('edit-item-image-upload');
        const preview = document.getElementById('edit-upload-preview');
        const previewImage = document.getElementById('edit-preview-image');
        const removeBtn = document.getElementById('edit-remove-preview');

        if (!dropArea || !fileInput) return;

        dropArea.addEventListener('click', () => {
            fileInput.click();
        });

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => {
                dropArea.classList.add('drag-over');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => {
                dropArea.classList.remove('drag-over');
            });
        });

        dropArea.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleEditFileSelect(files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) {
                handleEditFileSelect(e.target.files[0]);
            }
        });

        if (removeBtn) {
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                fileInput.value = '';
                if (preview) preview.style.display = 'none';
                if (dropArea) dropArea.style.display = 'flex';
            });
        }

        function handleEditFileSelect(file) {
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                showNotification('error', 'Error: Invalid file type. Allowed: JPG, PNG, GIF, WEBP');
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                showNotification('error', 'Error: File size exceeds 2MB limit');
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                if (previewImage) previewImage.src = e.target.result;
                if (preview) preview.style.display = 'flex';
                if (dropArea) dropArea.style.display = 'none';
            };
            reader.readAsDataURL(file);

            document.getElementById('edit-selected-default-image').value = '';
            document.querySelectorAll('#edit-default-images-grid .image-item').forEach(item => {
                item.classList.remove('selected');
            });
        }
    }

    // Adding CSS animation for row appearance
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeInRow {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    `;
    document.head.appendChild(style);

})();
