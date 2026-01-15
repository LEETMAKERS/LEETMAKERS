
(function () {
    'use strict';

    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function () {
        const table = document.querySelector('.inventory-table');
        if (!table) return;

        const headers = table.querySelectorAll('th .th-content');
        let currentSortColumn = null;
        let currentSortOrder = 'asc';

        // Add click event to each sortable header
        headers.forEach((header, index) => {
            // Check if this column is sortable (has an angle-down icon)
            const sortIcon = header.querySelector('.fa-angle-down');
            if (!sortIcon) return;

            header.addEventListener('click', () => {
                sortTable(index, header);
            });
        });

        function sortTable(columnIndex, headerElement) {
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            // Determine sort order
            if (currentSortColumn === columnIndex) {
                currentSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                currentSortOrder = 'asc';
            }

            currentSortColumn = columnIndex;

            // Remove all sorted classes from headers
            headers.forEach(h => {
                h.classList.remove('sorted', 'sorted-asc', 'sorted-desc');
            });

            // Add sorted class to current header
            headerElement.classList.add('sorted');
            headerElement.classList.add(currentSortOrder === 'asc' ? 'sorted-asc' : 'sorted-desc');

            // Sort rows based on column
            rows.sort((a, b) => {
                const aCell = a.querySelectorAll('td')[columnIndex];
                const bCell = b.querySelectorAll('td')[columnIndex];

                let aValue = getCellValue(aCell, columnIndex);
                let bValue = getCellValue(bCell, columnIndex);

                // Handle different data types
                if (columnIndex === 0 || columnIndex === 4) { // ID or Quantity
                    aValue = parseInt(aValue) || 0;
                    bValue = parseInt(bValue) || 0;
                    return currentSortOrder === 'asc' ? aValue - bValue : bValue - aValue;
                } else { // Text columns
                    aValue = aValue.toLowerCase();
                    bValue = bValue.toLowerCase();
                    if (currentSortOrder === 'asc') {
                        return aValue.localeCompare(bValue);
                    } else {
                        return bValue.localeCompare(aValue);
                    }
                }
            });

            // Clear tbody
            tbody.innerHTML = '';

            // Appending sorted rows with animation
            rows.forEach((row, index) => {
                row.style.animation = 'fadeInRow 0.3s ease forwards';
                row.style.animationDelay = `${index * 0.02}s`;
                tbody.appendChild(row);
            });
        }

        function getCellValue(cell, columnIndex) {
            // For mobile responsive tables with data-label
            const mobileLabel = cell.getAttribute('data-label');

            // Special handling for different column types
            if (columnIndex === 0) { // ID
                return cell.textContent.trim();
            } else if (columnIndex === 1) { // Item Name
                const itemName = cell.querySelector('.item-name span');
                return itemName ? itemName.textContent.trim() : cell.textContent.trim();
            } else if (columnIndex === 3) { // Category
                return cell.textContent.trim();
            } else if (columnIndex === 4) { // Quantity
                const quantityBadge = cell.querySelector('.quantity-badge');
                return quantityBadge ? quantityBadge.textContent.trim() : cell.textContent.trim();
            } else if (columnIndex === 5) { // Status
                const statusBadge = cell.querySelector('.status-badge');
                return statusBadge ? statusBadge.textContent.trim() : cell.textContent.trim();
            }

            return cell.textContent.trim();
        }
    });

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
