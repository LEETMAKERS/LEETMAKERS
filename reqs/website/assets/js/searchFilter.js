/**
 * Search & Filter Module for Inventory
 * Works with the navbar search input to filter inventory table.
 * 
 * Usage: Include this script on pages that need search functionality.
 */
(function () {
    'use strict';

    // Configuration for inventory page
    const config = {
        placeholder: 'Search inventory...',
        tableSelector: '.inventory-table',
        searchableColumns: [1, 3], // Item Name, Category (0-indexed)
        noResultsMessage: 'No items match your search',
        categoryColumn: 3
    };

    let searchInput = null;
    let debounceTimer = null;

    /**
     * Initialize the search module
     */
    function init() {
        // Only run on inventory page
        const path = window.location.pathname;
        const pageName = path.split('/').filter(Boolean).pop() || 'dashboard';

        if (pageName !== 'inventory') return;

        // Get the global search input from navbar
        searchInput = document.getElementById('global-search');
        if (!searchInput) return;

        // Update placeholder for inventory context
        searchInput.placeholder = config.placeholder;

        // Add search event listener with debounce
        searchInput.addEventListener('input', handleSearchInput);

        // Search button click handler
        const searchButton = document.getElementById('search-btn');
        if (searchButton) {
            searchButton.addEventListener('click', (e) => {
                e.preventDefault();
                performSearch(searchInput.value);
            });
        }

        // Handle Enter key
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch(searchInput.value);
            }
            if (e.key === 'Escape') {
                clearSearch();
            }
        });

        // Initialize filter pills
        initFilterPills();
    }

    /**
     * Handle search input with debounce
     */
    function handleSearchInput(e) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            performSearch(e.target.value);
        }, 200);
    }

    /**
     * Perform search on inventory table
     */
    function performSearch(query) {
        const searchTerm = query.toLowerCase().trim();
        searchInventoryTable(searchTerm);
    }

    /**
     * Search inventory table rows
     */
    function searchInventoryTable(searchTerm) {
        const table = document.querySelector(config.tableSelector);
        if (!table) return;

        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr:not(.search-empty-state):not(.pagination-empty-state)');
        const activeFilter = document.querySelector('.filter-pill.active')?.dataset.category || 'all';

        let visibleCount = 0;
        const totalRows = rows.length;

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let matchesSearch = false;
            let matchesFilter = true;

            // Check searchable columns
            config.searchableColumns.forEach(colIndex => {
                const cell = cells[colIndex];
                if (cell) {
                    const text = cell.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        matchesSearch = true;
                    }
                }
            });

            // Check category filter
            if (activeFilter !== 'all') {
                const categoryCell = cells[config.categoryColumn];
                if (categoryCell) {
                    const category = categoryCell.textContent.toLowerCase().trim();
                    matchesFilter = category === activeFilter.toLowerCase();
                }
            }

            // Empty search matches all
            if (searchTerm === '') {
                matchesSearch = true;
            }

            // Mark row as filtered or not (for pagination integration)
            if (matchesSearch && matchesFilter) {
                row.dataset.filtered = 'false';
                row.classList.remove('search-hidden');
                visibleCount++;
            } else {
                row.dataset.filtered = 'true';
                row.classList.add('search-hidden');
            }
        });

        // Handle empty state
        handleEmptyState(visibleCount, tbody);

        // Update search stats
        updateSearchStats(visibleCount, totalRows);

        // Notify pagination to refresh
        if (window.inventoryPagination) {
            window.inventoryPagination.refresh();
        }
    }

    /**
     * Handle empty state message
     */
    function handleEmptyState(visibleCount, tbody) {
        let emptyMessage = tbody.querySelector('.search-empty-state');

        if (visibleCount === 0) {
            if (!emptyMessage) {
                emptyMessage = document.createElement('tr');
                emptyMessage.className = 'search-empty-state';
                emptyMessage.innerHTML = `
                    <td colspan="10" style="text-align: center; padding: 2rem; color: var(--snd-txt-color);">
                        <i class="ri-search-line" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                        ${config.noResultsMessage}
                    </td>
                `;
                tbody.appendChild(emptyMessage);
            }
            emptyMessage.style.display = '';
        } else if (emptyMessage) {
            emptyMessage.style.display = 'none';
        }
    }

    /**
     * Update search stats (shown items count)
     */
    function updateSearchStats(visible, total) {
        let statsElement = document.querySelector('.search-stats');
        const filterPills = document.querySelector('.filter-pills');

        // Only show stats when filtering is active
        if (visible < total) {
            if (!statsElement) {
                statsElement = document.createElement('div');
                statsElement.className = 'search-stats';

                // Insert inside filter pills container (at the end)
                if (filterPills) {
                    filterPills.appendChild(statsElement);
                }
            }
            statsElement.innerHTML = `<span>Showing ${visible} of ${total} items</span>`;
            statsElement.style.display = 'inline-flex';
        } else if (statsElement) {
            statsElement.style.display = 'none';
        }
    }

    /**
     * Initialize category filter pills
     */
    function initFilterPills() {
        const table = document.querySelector(config.tableSelector);
        if (!table) return;

        // Extract unique categories from table
        const categories = new Set(['all']);
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const categoryCell = row.querySelectorAll('td')[config.categoryColumn];
            if (categoryCell) {
                const category = categoryCell.textContent.trim();
                if (category) {
                    categories.add(category);
                }
            }
        });

        // Create filter pills container if we have categories
        if (categories.size > 1) {
            createFilterPills(Array.from(categories));
        }
    }

    /**
     * Create filter pills UI
     */
    function createFilterPills(categories) {
        const container = document.querySelector('.inventory-container');
        const statsSection = document.querySelector('.inventory-stats');
        const header = document.querySelector('.inventory-header');

        if (!container) return;

        // Check if pills already exist
        if (document.querySelector('.filter-pills')) return;

        const pillsContainer = document.createElement('div');
        pillsContainer.className = 'filter-pills';
        pillsContainer.innerHTML = `
            <span class="filter-label"><i class="ri-filter-3-line"></i> Filter:</span>
            ${categories.map(cat => `
                <button class="filter-pill ${cat === 'all' ? 'active' : ''}" data-category="${cat}">
                    ${cat === 'all' ? 'All' : cat}
                </button>
            `).join('')}
        `;

        // Insert after stats section if exists (admin), otherwise after header (member)
        if (statsSection) {
            statsSection.insertAdjacentElement('afterend', pillsContainer);
        } else if (header) {
            header.insertAdjacentElement('afterend', pillsContainer);
        }

        // Add click handlers
        pillsContainer.querySelectorAll('.filter-pill').forEach(pill => {
            pill.addEventListener('click', () => {
                // Update active state
                pillsContainer.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');

                // Re-run search with current search term
                performSearch(searchInput?.value || '');
            });
        });
    }

    /**
     * Clear search and reset filters
     */
    function clearSearch() {
        if (searchInput) {
            searchInput.value = '';
            performSearch('');
        }

        // Reset filter pills to "All"
        const allPill = document.querySelector('.filter-pill[data-category="all"]');
        if (allPill) {
            document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
            allPill.classList.add('active');
        }
    }

    // Expose clear function globally
    window.clearSearch = clearSearch;

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
