/**
 * Search JavaScript
 * Handles advanced search functionality and filtering
 */

class SearchManager {
    constructor() {
        this.filters = {
            types: [],
            statuses: [],
            fileTypes: [],
            dateFrom: null,
            dateTo: null
        };
        this.init();
    }
    
    init() {
        this.attachEventListeners();
        this.loadSavedFilters();
    }
    
    attachEventListeners() {
        // Main search input
        const mainSearch = document.getElementById('main-search');
        if (mainSearch) {
            mainSearch.addEventListener('input', debounce(() => this.performSearch(), 500));
            mainSearch.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.performSearch();
                }
            });
        }
        
        // Filter checkboxes
        document.querySelectorAll('.filter-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => this.updateFilters());
        });
        
        // Date filters
        document.querySelectorAll('.date-filter').forEach(dateInput => {
            dateInput.addEventListener('change', () => this.updateFilters());
        });
        
        // Sort dropdown
        const sortSelect = document.querySelector('[data-sort-select]');
        if (sortSelect) {
            sortSelect.addEventListener('change', () => this.performSearch());
        }
        
        // View toggle
        document.querySelectorAll('[data-view-toggle]').forEach(btn => {
            btn.addEventListener('click', () => this.toggleView(btn.dataset.viewToggle));
        });
    }
    
    async performSearch() {
        const query = document.getElementById('main-search')?.value || '';
        const sortBy = document.querySelector('[data-sort-select]')?.value || 'relevance';
        
        const searchParams = {
            q: query,
            sort: sortBy,
            ...this.filters
        };
        
        this.showLoadingState();
        
        try {
            const params = new URLSearchParams(searchParams);
            const baseUrl = window.App?.config?.baseUrl || '';
            const response = await fetch(`${baseUrl}/modules/search/controllers/SearchController.php?${params.toString()}`);
            const result = await response.json();
            
            if (result.success) {
                this.displayResults(result.data);
                this.updateResultCount(result.total);
            } else {
                toast.show(result.message || 'Search failed', 'error');
            }
        } catch (error) {
            console.error('Search error:', error);
            toast.show('Search failed. Please try again.', 'error');
        } finally {
            this.hideLoadingState();
        }
    }
    
    updateFilters() {
        // Document types
        this.filters.types = Array.from(
            document.querySelectorAll('input[name="document_type"]:checked')
        ).map(cb => cb.value);
        
        // Statuses
        this.filters.statuses = Array.from(
            document.querySelectorAll('input[name="status"]:checked')
        ).map(cb => cb.value);
        
        // File types
        this.filters.fileTypes = Array.from(
            document.querySelectorAll('input[name="file_type"]:checked')
        ).map(cb => cb.value);
        
        // Dates
        this.filters.dateFrom = document.querySelector('input[name="date_from"]')?.value;
        this.filters.dateTo = document.querySelector('input[name="date_to"]')?.value;
        
        this.saveFilters();
    }
    
    saveFilters() {
        localStorage.setItem('searchFilters', JSON.stringify(this.filters));
    }
    
    loadSavedFilters() {
        const saved = localStorage.getItem('searchFilters');
        if (saved) {
            try {
                this.filters = JSON.parse(saved);
                this.applyFiltersToUI();
            } catch (error) {
                console.error('Failed to load saved filters:', error);
            }
        }
    }
    
    applyFiltersToUI() {
        // Apply document types
        this.filters.types.forEach(type => {
            const checkbox = document.querySelector(`input[name="document_type"][value="${type}"]`);
            if (checkbox) checkbox.checked = true;
        });
        
        // Apply statuses
        this.filters.statuses.forEach(status => {
            const checkbox = document.querySelector(`input[name="status"][value="${status}"]`);
            if (checkbox) checkbox.checked = true;
        });
        
        // Apply dates
        if (this.filters.dateFrom) {
            const fromInput = document.querySelector('input[name="date_from"]');
            if (fromInput) fromInput.value = this.filters.dateFrom;
        }
        if (this.filters.dateTo) {
            const toInput = document.querySelector('input[name="date_to"]');
            if (toInput) toInput.value = this.filters.dateTo;
        }
    }
    
    clearFilters() {
        this.filters = {
            types: [],
            statuses: [],
            fileTypes: [],
            dateFrom: null,
            dateTo: null
        };
        
        document.querySelectorAll('.filter-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.date-filter').forEach(input => input.value = '');
        
        localStorage.removeItem('searchFilters');
        this.performSearch();
    }
    
    displayResults(results) {
        const container = document.getElementById('search-results');
        if (!container) return;
        
        if (results.length === 0) {
            container.innerHTML = `
                <div class="bg-white rounded-xl shadow-md p-12 text-center">
                    <i class="bi bi-search text-6xl text-gray-400 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">No results found</h3>
                    <p class="text-gray-600">Try adjusting your search terms or filters</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = results.map(doc => this.createResultCard(doc)).join('');
    }
    
    createResultCard(doc) {
        const fileIcons = {
            pdf: 'file-pdf',
            doc: 'file-word',
            docx: 'file-word',
            xls: 'file-excel',
            xlsx: 'file-excel',
            ppt: 'file-ppt',
            pptx: 'file-ppt'
        };
        
        const statusBadges = {
            approved: 'badge-success',
            pending: 'badge-warning',
            draft: 'badge-info',
            archived: 'badge-danger'
        };
        
        const icon = fileIcons[doc.file_extension] || 'file-earmark';
        const iconColor = doc.file_extension === 'pdf' ? 'red' : 'blue';
        
        return `
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
                <div class="flex items-start gap-4">
                    <div class="bg-${iconColor}-100 rounded-lg p-3">
                        <i class="bi bi-${icon} text-${iconColor}-600 text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800 mb-1">
                                    ${doc.title}
                                </h3>
                                <div class="flex items-center gap-3 text-sm text-gray-600">
                                    <span><i class="bi bi-hash mr-1"></i>${doc.reference_number}</span>
                                    <span><i class="bi bi-calendar3 mr-1"></i>${formatDate(doc.document_date)}</span>
                                    <span><i class="bi bi-hdd mr-1"></i>${formatFileSize(doc.file_size)}</span>
                                </div>
                            </div>
                            <span class="badge ${statusBadges[doc.status]}">${doc.status}</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">${doc.description || 'No description available'}</p>
                        <div class="flex items-center justify-between">
                            <div class="flex gap-2">
                                ${doc.tags?.map(tag => `<span class="badge badge-info">${tag}</span>`).join('') || ''}
                            </div>
                            <div class="flex gap-2">
                                <button onclick="viewDocument(${doc.id})" class="px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded-lg">
                                    <i class="bi bi-eye mr-1"></i>View
                                </button>
                                <button onclick="downloadDocument(${doc.id})" class="px-3 py-1.5 text-sm text-green-600 hover:bg-green-50 rounded-lg">
                                    <i class="bi bi-download mr-1"></i>Download
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    updateResultCount(total) {
        const countElement = document.querySelector('[data-result-count]');
        if (countElement) {
            countElement.textContent = total.toLocaleString();
        }
    }
    
    toggleView(view) {
        const container = document.getElementById('search-results');
        if (!container) return;
        
        if (view === 'grid') {
            container.classList.add('grid', 'grid-cols-2', 'gap-4');
            container.classList.remove('space-y-4');
        } else {
            container.classList.remove('grid', 'grid-cols-2', 'gap-4');
            container.classList.add('space-y-4');
        }
    }
    
    showLoadingState() {
        const container = document.getElementById('search-results');
        if (container) {
            container.innerHTML = `
                <div class="bg-white rounded-xl shadow-md p-12 text-center">
                    <i class="bi bi-hourglass-split text-6xl text-blue-600 mb-4 animate-pulse"></i>
                    <p class="text-gray-600">Searching documents...</p>
                </div>
            `;
        }
    }
    
    hideLoadingState() {
        // Results will be displayed by displayResults()
    }
}

// Initialize Search Manager
const searchManager = new SearchManager();

// Quick filter buttons
document.querySelectorAll('[data-quick-filter]').forEach(btn => {
    btn.addEventListener('click', function() {
        const filterType = this.dataset.quickFilter;
        applyQuickFilter(filterType);
    });
});

function applyQuickFilter(type) {
    const filterMap = {
        'this-month': () => {
            const now = new Date();
            const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
            searchManager.filters.dateFrom = firstDay.toISOString().split('T')[0];
            searchManager.filters.dateTo = now.toISOString().split('T')[0];
        },
        'approved': () => {
            searchManager.filters.statuses = ['approved'];
        },
        'ordinances': () => {
            searchManager.filters.types = ['ordinance'];
        },
        'high-priority': () => {
            // Custom filter logic
        }
    };
    
    if (filterMap[type]) {
        filterMap[type]();
        searchManager.performSearch();
    }
}

// View and Download functions
function viewDocument(id) {
    window.location.href = `/modules/document-management/views/view.php?id=${id}`;
}

function downloadDocument(id) {
    window.location.href = `/modules/document-management/controllers/DownloadController.php?id=${id}`;
}

// Clear filters button
document.querySelector('[data-clear-filters]')?.addEventListener('click', () => {
    searchManager.clearFilters();
});

console.log('Search JS Loaded');
