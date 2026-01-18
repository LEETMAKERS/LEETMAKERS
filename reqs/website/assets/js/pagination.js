/**
 * Reusable Pagination Module
 * Provides client-side pagination for tables and lists.
 * 
 * Usage: 
 *   const paginator = new Pagination({
 *       containerSelector: '.inventory-table tbody',
 *       itemSelector: 'tr',
 *       paginationContainer: '.pagination-container',
 *       itemsPerPage: 10
 *   });
 */
(function () {
    'use strict';

    class Pagination {
        constructor(options = {}) {
            // Default configuration
            this.config = {
                containerSelector: 'tbody',
                itemSelector: 'tr',
                paginationContainer: '.pagination-container',
                itemsPerPage: 10,
                itemsPerPageOptions: [5, 10, 25, 50, 100],
                maxVisiblePages: 5,
                showItemsPerPage: true,
                showPageInfo: true,
                showFirstLast: true,
                onPageChange: null,
                ...options
            };

            this.currentPage = 1;
            this.totalItems = 0;
            this.totalPages = 0;
            this.items = [];
            this.filteredItems = [];

            // Support dynamic itemsPerPage
            this.dynamicPerPage = this.config.itemsPerPage === 'auto';

            this.init();
        }

        /**
         * Initialize pagination
         */
        init() {
            this.container = document.querySelector(this.config.containerSelector);
            this.paginationContainer = document.querySelector(this.config.paginationContainer);

            if (!this.container || !this.paginationContainer) {
                console.warn('Pagination: Container or pagination element not found');
                return;
            }

            // Get all items (exclude empty state rows)
            this.refreshItems();

            // Create pagination UI
            this.createPaginationUI();

            // Initial render
            this.goToPage(1);

            // Listen for filter changes (integration with searchFilter.js)
            this.setupFilterListener();
        }

        /**
         * Refresh items list from DOM
         */
        refreshItems() {
            const allItems = this.container.querySelectorAll(this.config.itemSelector);
            this.items = Array.from(allItems).filter(item =>
                !item.classList.contains('search-empty-state') &&
                !item.classList.contains('pagination-empty-state')
            );

            // Filter out items hidden by search/filter
            this.filteredItems = this.items.filter(item =>
                item.dataset.filtered !== 'true' &&
                !item.classList.contains('search-hidden')
            );

            this.totalItems = this.filteredItems.length;

            // Calculate dynamic items per page if set to 'auto'
            if (this.dynamicPerPage) {
                this.config.itemsPerPage = this.calculateOptimalPerPage(this.totalItems);
            }

            this.totalPages = Math.ceil(this.totalItems / this.config.itemsPerPage);

            // Reset to page 1 if current page exceeds total
            if (this.currentPage > this.totalPages && this.totalPages > 0) {
                this.currentPage = 1;
            }
        }

        /**
         * Calculate optimal items per page based on total items
         * - 15 or less: show all (no pagination needed)
         * - 16-50: 10 per page
         * - 51+: 15 per page
         */
        calculateOptimalPerPage(totalItems) {
            if (totalItems <= 15) return totalItems || 10;
            if (totalItems <= 50) return 10;
            return 15;
        }

        /**
         * Create pagination UI elements
         */
        createPaginationUI() {
            this.paginationContainer.innerHTML = '';
            this.paginationContainer.className = 'pagination-wrapper';

            // Left section: Items per page selector
            if (this.config.showItemsPerPage) {
                const perPageSection = document.createElement('div');
                perPageSection.className = 'pagination-per-page';
                perPageSection.innerHTML = `
                    <label>Show</label>
                    <select class="per-page-select">
                        ${this.config.itemsPerPageOptions.map(opt =>
                    `<option value="${opt}" ${opt === this.config.itemsPerPage ? 'selected' : ''}>${opt}</option>`
                ).join('')}
                    </select>
                    <span>entries</span>
                `;
                this.paginationContainer.appendChild(perPageSection);

                // Event listener for items per page change
                const select = perPageSection.querySelector('.per-page-select');
                select.addEventListener('change', (e) => {
                    this.config.itemsPerPage = parseInt(e.target.value);
                    this.totalPages = Math.ceil(this.totalItems / this.config.itemsPerPage);
                    this.goToPage(1);
                });
            }

            // Center section: Page navigation
            const navSection = document.createElement('div');
            navSection.className = 'pagination-nav';
            this.paginationContainer.appendChild(navSection);
            this.navSection = navSection;

            // Right section: Page info
            if (this.config.showPageInfo) {
                const infoSection = document.createElement('div');
                infoSection.className = 'pagination-info';
                this.paginationContainer.appendChild(infoSection);
                this.infoSection = infoSection;
            }

            this.renderNavigation();
        }

        /**
         * Render page navigation buttons
         */
        renderNavigation() {
            if (!this.navSection) return;

            this.navSection.innerHTML = '';

            // First button
            if (this.config.showFirstLast) {
                const firstBtn = this.createButton('<i class="ri-arrow-left-double-line"></i>', 1, this.currentPage === 1, false, true);
                firstBtn.title = 'First page';
                firstBtn.classList.add('pagination-first');
                this.navSection.appendChild(firstBtn);
            }

            // Previous button
            const prevBtn = this.createButton('<i class="ri-arrow-left-s-line"></i>', this.currentPage - 1, this.currentPage === 1, false, true);
            prevBtn.title = 'Previous page';
            prevBtn.classList.add('pagination-prev');
            this.navSection.appendChild(prevBtn);

            // Page numbers
            const pageNumbers = this.getVisiblePageNumbers();
            pageNumbers.forEach(pageNum => {
                if (pageNum === '...') {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'pagination-ellipsis';
                    ellipsis.textContent = '...';
                    this.navSection.appendChild(ellipsis);
                } else {
                    const pageBtn = this.createButton(pageNum, pageNum, false, pageNum === this.currentPage);
                    this.navSection.appendChild(pageBtn);
                }
            });

            // Next button
            const nextBtn = this.createButton('<i class="ri-arrow-right-s-line"></i>', this.currentPage + 1, this.currentPage === this.totalPages, false, true);
            nextBtn.title = 'Next page';
            nextBtn.classList.add('pagination-next');
            this.navSection.appendChild(nextBtn);

            // Last button
            if (this.config.showFirstLast) {
                const lastBtn = this.createButton('<i class="ri-arrow-right-double-line"></i>', this.totalPages, this.currentPage === this.totalPages, false, true);
                lastBtn.title = 'Last page';
                lastBtn.classList.add('pagination-last');
                this.navSection.appendChild(lastBtn);
            }
        }

        /**
         * Create a pagination button
         */
        createButton(content, page, disabled = false, active = false, isIcon = false) {
            const btn = document.createElement('button');
            btn.className = 'pagination-btn';
            if (isIcon) {
                btn.innerHTML = content;
            } else {
                btn.textContent = content;
            }
            btn.disabled = disabled || this.totalPages === 0;

            if (active) btn.classList.add('active');
            if (disabled) btn.classList.add('disabled');

            btn.addEventListener('click', () => {
                if (!disabled && page >= 1 && page <= this.totalPages) {
                    this.goToPage(page);
                }
            });

            return btn;
        }

        /**
         * Get array of visible page numbers with ellipsis
         */
        getVisiblePageNumbers() {
            const pages = [];
            const maxVisible = this.getResponsiveMaxPages();
            const total = this.totalPages;
            const current = this.currentPage;

            if (total <= maxVisible) {
                // Show all pages
                for (let i = 1; i <= total; i++) {
                    pages.push(i);
                }
            } else {
                // Always show first page
                pages.push(1);

                // Calculate range around current page
                let start = Math.max(2, current - Math.floor((maxVisible - 3) / 2));
                let end = Math.min(total - 1, start + maxVisible - 4);

                // Adjust start if end is at max
                if (end === total - 1) {
                    start = Math.max(2, end - (maxVisible - 4));
                }

                // Add ellipsis before if needed
                if (start > 2) {
                    pages.push('...');
                }

                // Add middle pages
                for (let i = start; i <= end; i++) {
                    pages.push(i);
                }

                // Add ellipsis after if needed
                if (end < total - 1) {
                    pages.push('...');
                }

                // Always show last page
                if (total > 1) {
                    pages.push(total);
                }
            }

            return pages;
        }

        /**
         * Get responsive max visible pages based on screen width
         */
        getResponsiveMaxPages() {
            const width = window.innerWidth;
            if (width <= 480) return 3;
            if (width <= 768) return 5;
            return this.config.maxVisiblePages;
        }

        /**
         * Navigate to a specific page
         */
        goToPage(page) {
            if (page < 1) page = 1;
            if (page > this.totalPages) page = this.totalPages;
            if (this.totalPages === 0) page = 1;

            this.currentPage = page;

            // Calculate start and end indices
            const startIndex = (page - 1) * this.config.itemsPerPage;
            const endIndex = startIndex + this.config.itemsPerPage;

            // First, hide all items
            this.items.forEach(item => {
                item.style.display = 'none';
            });

            // Show only filtered items on current page
            this.filteredItems.forEach((item, index) => {
                if (index >= startIndex && index < endIndex) {
                    item.style.display = '';
                    item.style.animation = 'fadeInRow 0.3s ease forwards';
                }
            });

            // Update navigation
            this.renderNavigation();

            // Update page info
            this.updatePageInfo(startIndex, endIndex);

            // Callback
            if (typeof this.config.onPageChange === 'function') {
                this.config.onPageChange(page, this.totalPages);
            }
        }

        /**
         * Update page info text
         */
        updatePageInfo(startIndex, endIndex) {
            if (!this.infoSection) return;

            if (this.totalItems === 0) {
                this.infoSection.textContent = 'No entries';
            } else {
                const showingEnd = Math.min(endIndex, this.totalItems);
                this.infoSection.textContent = `Showing ${startIndex + 1}-${showingEnd} of ${this.totalItems}`;
            }
        }

        /**
         * Setup listener for filter changes (integration with searchFilter.js)
         */
        setupFilterListener() {
            // Handle window resize for responsive page numbers
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    this.renderNavigation();
                }, 150);
            });
        }

        /**
         * Public method to manually update pagination (call after AJAX updates or filter changes)
         */
        refresh() {
            this.refreshItems();
            this.totalPages = Math.ceil(this.totalItems / this.config.itemsPerPage);
            this.goToPage(1);
        }

        /**
         * Set items per page programmatically
         */
        setItemsPerPage(count) {
            this.config.itemsPerPage = count;
            this.totalPages = Math.ceil(this.totalItems / this.config.itemsPerPage);

            // Update select if it exists
            const select = this.paginationContainer.querySelector('.per-page-select');
            if (select) select.value = count;

            this.goToPage(1);
        }

        /**
         * Get current state
         */
        getState() {
            return {
                currentPage: this.currentPage,
                totalPages: this.totalPages,
                totalItems: this.totalItems,
                itemsPerPage: this.config.itemsPerPage
            };
        }
    }

    // Expose globally
    window.Pagination = Pagination;

})();
