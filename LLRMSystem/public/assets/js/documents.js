/**
 * Document Management JavaScript
 * Handles document CRUD operations, filtering, and interactions
 */
console.log('Documents JS Version 2.0 Loading...');

// Global handles for HTML event attributes (Defined at top for immediate availability)
function toggleAdvancedFilters() {
    console.log('toggleAdvancedFilters triggered');
    const panel = document.getElementById('advanced-filters-panel');
    const chevron = document.getElementById('advanced-filters-chevron');
    if (panel) {
        const isHidden = panel.classList.toggle('hidden');
        if (chevron) {
            chevron.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    }
}

function applyFilters() {
    if (window.docManager) window.docManager.applyFilters();
}

function applyAdvancedFilters() {
    if (window.docManager) window.docManager.applyAdvancedFilters();
}

function clearAdvancedFilters() {
    if (window.docManager) window.docManager.clearFilters();
}

function toggleSelectAll(el) {
    if (window.docManager) window.docManager.selectAll(el.checked);
}

// Global variables
let selectedDocuments = [];

// Document Table Interactions
class DocumentManager {
    constructor() {
        this.selectedDocuments = new Set();
        this.init();
    }

    init() {
        try {
            this.attachEventListeners();
            this.initializeFilters();
            this.initializeAdvancedFilters();
            this.initializeMobileFilterToggle();
            this.populateAdvancedFilters(); // Ensure existing filters are shown
        } catch (e) {
            console.error('Error in DocumentManager init:', e);
        }
    }

    // Mobile filter toggle functionality
    initializeMobileFilterToggle() {
        const filterToggle = document.getElementById('mobile-filter-toggle');
        const filtersSection = document.getElementById('filters-section');
        const filterToggleIcon = document.getElementById('filter-toggle-icon');

        // Auto-show if any filters are active
        const params = new URLSearchParams(window.location.search);
        const hasFilters = Array.from(params.keys()).some(k => ['search', 'type', 'status', 'date_from', 'date_to', 'tags', 'category', 'reference'].includes(k) && params.get(k) !== '');

        if (hasFilters && filtersSection && window.innerWidth < 768) {
            filtersSection.classList.remove('hidden');
            if (filterToggleIcon) filterToggleIcon.style.transform = 'rotate(180deg)';
        }

        if (filterToggle && filtersSection) {
            filterToggle.addEventListener('click', () => {
                const isHidden = filtersSection.classList.contains('hidden');

                if (isHidden) {
                    filtersSection.classList.remove('hidden');
                    filtersSection.classList.add('animate-fade-in-up');
                    if (filterToggleIcon) {
                        filterToggleIcon.style.transform = 'rotate(180deg)';
                    }
                } else {
                    filtersSection.classList.add('hidden');
                    filtersSection.classList.remove('animate-fade-in-up');
                    if (filterToggleIcon) {
                        filterToggleIcon.style.transform = 'rotate(0deg)';
                    }
                }
            });
        }
    }

    attachEventListeners() {
        // Select all checkbox
        const selectAllCheckbox = document.querySelector('table thead input[type="checkbox"]');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => this.selectAll(e.target.checked));
        }

        // Individual checkboxes
        document.querySelectorAll('.document-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => this.toggleSelection(e.target));
        });

        // Delete buttons
        document.querySelectorAll('[data-action="delete"]').forEach(btn => {
            btn.addEventListener('click', (e) => this.deleteDocument(e.target.dataset.id));
        });

        // View buttons
        document.querySelectorAll('[data-action="view"]').forEach(btn => {
            btn.addEventListener('click', (e) => this.viewDocument(e.target.dataset.id));
        });

        // Download buttons
        document.querySelectorAll('[data-action="download"]').forEach(btn => {
            btn.addEventListener('click', (e) => this.downloadDocument(e.target.dataset.id));
        });

        // Bulk action buttons
        const bulkDownloadBtn = Array.from(document.querySelectorAll('button')).find(btn =>
            btn.querySelector('.bi-download') && (btn.title.includes('Download') || btn.textContent.includes('Download'))
        );
        if (bulkDownloadBtn) {
            bulkDownloadBtn.addEventListener('click', () => this.bulkDownload());
        }

        const bulkDeleteBtns = Array.from(document.querySelectorAll('button')).filter(btn =>
            btn.querySelector('.bi-trash') && (btn.title.includes('Selected') || btn.title.includes('Delete') || btn.textContent.includes('Selected') || btn.textContent.includes('Delete'))
        );
        bulkDeleteBtns.forEach(btn => {
            btn.addEventListener('click', () => this.bulkDelete());
        });
    }

    initializeFilters() {
        const searchInput = document.getElementById('main-search');
        const typeFilter = document.getElementById('type-filter-input') || document.getElementById('type-filter');
        const statusFilter = document.getElementById('status-filter-input') || document.getElementById('status-filter');

        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.applyFilters();
                }, 800);
            });

            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.applyFilters();
                }
            });
        }

        // Dropdowns no longer auto-refresh; Apply Filters button triggers refresh

        // Check if we should auto-show advanced filters
        const params = new URLSearchParams(window.location.search);
        const advancedKeys = ['date_from', 'date_to', 'file_size', 'tags', 'category', 'reference'];
        const hasAdvanced = advancedKeys.some(key => params.has(key) && params.get(key) !== '');

        if (hasAdvanced) {
            this.toggleAdvancedFilters();
        }
    }

    initializeAdvancedFilters() {
        // Handled by inline onclick for reliability
    }

    toggleAdvancedFilters() {
        const advancedPanel = document.getElementById('advanced-filters-panel');
        const chevron = document.getElementById('advanced-filters-chevron');

        if (!advancedPanel) return;

        const isHidden = advancedPanel.classList.contains('hidden');

        if (isHidden) {
            advancedPanel.classList.remove('hidden');
            advancedPanel.style.display = 'block'; // Force display if class toggle fails
            if (chevron) {
                chevron.style.transform = 'rotate(180deg)';
                chevron.classList.add('text-red-600');
            }
        } else {
            advancedPanel.classList.add('hidden');
            advancedPanel.style.display = 'none';
            if (chevron) {
                chevron.style.transform = 'rotate(0deg)';
                chevron.classList.remove('text-red-600');
            }
        }
    }

    applyFilters() {
        const searchInput = document.getElementById('main-search');
        const typeFilter = document.getElementById('type-filter-input') || document.getElementById('type-filter');
        const statusFilter = document.getElementById('status-filter-input') || document.getElementById('status-filter');

        const params = new URLSearchParams(window.location.search);

        if (searchInput && searchInput.value) {
            params.set('search', searchInput.value);
        } else {
            params.delete('search');
        }

        if (typeFilter && typeFilter.value) {
            params.set('type', typeFilter.value);
        } else {
            params.delete('type');
        }

        if (statusFilter && statusFilter.value) {
            params.set('status', statusFilter.value);
        } else {
            params.delete('status');
        }

        // Preserve advanced filters if they exist
        const advancedKeys = ['date_from', 'date_to', 'file_size', 'tags', 'category', 'reference', 'compliance_status'];
        advancedKeys.forEach(key => {
            const val = params.get(key);
            if (val) params.set(key, val);
        });

        // Reset page when filters change
        params.delete('page');

        // Reload page with filters
        window.location.href = window.location.pathname + '?' + params.toString();
    }

    applyAdvancedFilters() {
        const params = new URLSearchParams(window.location.search);

        const dateFrom = document.getElementById('filter-date-from');
        const dateTo = document.getElementById('filter-date-to');
        const reference = document.getElementById('filter-reference');
        const tags = document.getElementById('filter-tags');
        const compliance = document.getElementById('filter-compliance');

        if (dateFrom && dateFrom.value) params.set('date_from', dateFrom.value);
        else params.delete('date_from');

        if (dateTo && dateTo.value) params.set('date_to', dateTo.value);
        else params.delete('date_to');

        if (reference && reference.value) params.set('reference', reference.value);
        else params.delete('reference');

        if (tags && tags.value) params.set('tags', tags.value);
        else params.delete('tags');

        if (compliance && compliance.value) params.set('compliance_status', compliance.value);
        else params.delete('compliance_status');

        // Reset page when filters change
        params.delete('page');

        window.location.href = window.location.pathname + '?' + params.toString();
    }

    clearFilters() {
        const params = new URLSearchParams(window.location.search);
        const advancedKeys = ['date_from', 'date_to', 'file_size', 'tags', 'category', 'reference', 'compliance_status'];
        advancedKeys.forEach(key => params.delete(key));

        // Reset page when filters are cleared
        params.delete('page');

        window.location.href = window.location.pathname + '?' + params.toString();
    }

    populateAdvancedFilters() {
        const params = new URLSearchParams(window.location.search);

        const map = {
            'filter-date-from': 'date_from',
            'filter-date-to': 'date_to',
            'filter-file-size': 'file_size',
            'filter-tags': 'tags',
            'filter-category': 'category',
            'filter-reference': 'reference',
            'filter-compliance': 'compliance_status'
        };

        for (const [id, param] of Object.entries(map)) {
            const el = document.getElementById(id);
            if (el && params.has(param)) {
                el.value = params.get(param);
            }
        }
    }

    createAdvancedFiltersPanel() {
        // This is now handled in the PHP template for better performance
        return document.getElementById('advanced-filters-panel');
    }

    selectAll(checked) {
        // Target all document checkboxes (desktop and mobile)
        document.querySelectorAll('.document-checkbox').forEach(checkbox => {
            checkbox.checked = checked;
            this.toggleSelection(checkbox, false); // Pass false to avoid redundant visibility updates
        });
        this.updateBulkActionsVisibility();

        // Update both "Select All" checkboxes if they exist
        const topCheckbox = document.getElementById('select-all-top');
        if (topCheckbox) topCheckbox.checked = checked;
    }

    toggleSelection(checkbox, updateVisibility = true) {
        const documentId = checkbox.value;
        const desktopRow = checkbox.closest('tr');
        const mobileCard = checkbox.closest('.mobile-doc-card');

        if (checkbox.checked) {
            this.selectedDocuments.add(documentId);
            if (!selectedDocuments.includes(documentId)) {
                selectedDocuments.push(documentId);
            }

            // Visual indicators
            if (desktopRow) {
                desktopRow.classList.add('bg-blue-50', 'dark:bg-blue-900/10');
            }
            if (mobileCard) {
                mobileCard.classList.add('bg-red-50/50', 'border-l-4', 'border-red-600');
            }
        } else {
            this.selectedDocuments.delete(documentId);
            selectedDocuments = selectedDocuments.filter(id => id !== documentId);

            // Visual indicators
            if (desktopRow) {
                desktopRow.classList.remove('bg-blue-50', 'dark:bg-blue-900/10');
            }
            if (mobileCard) {
                mobileCard.classList.remove('bg-red-50/50', 'border-l-4', 'border-red-600');
            }

            // Uncheck "Select All" if any item is unchecked
            const selectAllTop = document.getElementById('select-all-top');
            if (selectAllTop) selectAllTop.checked = false;
        }

        if (updateVisibility) {
            this.updateBulkActionsVisibility();
        }

        // Update selection count text
        const countText = document.getElementById('selected-count');
        if (countText) {
            const size = this.selectedDocuments.size;
            if (size > 0) {
                countText.innerHTML = `<span class="text-red-600 font-black">${size} selected</span> of ${document.querySelectorAll('.document-checkbox').length / (window.innerWidth < 768 ? 1 : 2)} documents`;
                // Note: The denominator trick accounts for duplicate checkboxes if both desktop/mobile are rendered.
                // Better approach:
                const total = document.querySelectorAll('.md\\:hidden .document-checkbox').length || document.querySelectorAll('tbody .document-checkbox').length;
                countText.innerHTML = `<span class="text-red-600 font-black">${size} selected</span> of ${total} documents`;
            } else {
                const total = document.querySelectorAll('.md\\:hidden .document-checkbox').length || document.querySelectorAll('tbody .document-checkbox').length;
                countText.innerHTML = `<span id="total-docs">${total}</span> documents found`;
            }
        }
    }

    updateBulkActionsVisibility() {
        // More compatible selectors than :has()
        const bulkDownloadBtn = Array.from(document.querySelectorAll('button')).find(btn =>
            btn.querySelector('.bi-download') && (btn.title.includes('Download') || btn.textContent.includes('Download'))
        );
        const bulkDeleteBtn = Array.from(document.querySelectorAll('button')).filter(btn =>
            btn.querySelector('.bi-trash') && (btn.title.includes('Selected') || btn.textContent.includes('Selected'))
        );

        const hasSelected = this.selectedDocuments.size > 0;

        if (bulkDownloadBtn) {
            bulkDownloadBtn.disabled = !hasSelected;
            bulkDownloadBtn.classList.toggle('opacity-50', !hasSelected);
            bulkDownloadBtn.classList.toggle('cursor-not-allowed', !hasSelected);
        }

        bulkDeleteBtn.forEach(btn => {
            btn.disabled = !hasSelected;
            btn.classList.toggle('opacity-50', !hasSelected);
            btn.classList.toggle('cursor-not-allowed', !hasSelected);
        });
    }

    async deleteDocument(documentId) {
        if (!confirm('Are you sure you want to delete this document?')) {
            return;
        }

        try {
            const response = await fetch(App.apiUrl('documents', 'delete.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: documentId })
            });

            const result = await response.json();

            if (result.success) {
                showNotification('Document deleted successfully', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(result.error || 'Failed to delete document', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            showNotification('An error occurred while deleting', 'error');
        }
    }

    viewDocument(documentId) {
        window.location.href = App.config.urls.documents + `/views/view.php?id=${documentId}`;
    }

    async downloadDocument(documentId) {
        try {
            showNotification('Preparing download...', 'info');
            window.location.href = App.apiUrl('documents', `download.php?id=${documentId}`);
        } catch (error) {
            console.error('Download error:', error);
            showNotification('Failed to download document', 'error');
        }
    }

    async bulkDelete() {
        if (this.selectedDocuments.size === 0) {
            showNotification('No documents selected', 'warning');
            return;
        }

        if (!confirm(`Delete ${this.selectedDocuments.size} selected documents? This action cannot be undone.`)) {
            return;
        }

        try {
            const response = await fetch(App.apiUrl('documents', 'bulk-delete.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ document_ids: Array.from(this.selectedDocuments) })
            });

            const result = await response.json();

            if (result.success) {
                showNotification(result.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(result.error || 'Failed to delete documents', 'error');
            }
        } catch (error) {
            console.error('Bulk delete error:', error);
            showNotification('An error occurred', 'error');
        }
    }

    async bulkDownload() {
        if (this.selectedDocuments.size === 0) {
            showNotification('No documents selected', 'warning');
            return;
        }

        showNotification(`Preparing to download ${this.selectedDocuments.size} document(s)...`, 'info');

        // Download each document
        Array.from(this.selectedDocuments).forEach((id, index) => {
            setTimeout(() => {
                const link = document.createElement('a');
                link.href = App.apiUrl('documents', `download.php?id=${id}`);
                link.download = '';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }, index * 500); // Stagger downloads by 500ms
        });
    }
}

// Initialize Document Manager
window.docManager = new DocumentManager();

// Ensure global compatibility
window.toggleAdvancedFilters = toggleAdvancedFilters;
window.applyFilters = applyFilters;
window.applyAdvancedFilters = applyAdvancedFilters;
window.clearAdvancedFilters = clearAdvancedFilters;
window.toggleSelectAll = toggleSelectAll;

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notification
    const existing = document.querySelector('.notification-toast');
    if (existing) {
        existing.remove();
    }

    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };

    const icons = {
        success: 'bi-check-circle',
        error: 'bi-x-circle',
        warning: 'bi-exclamation-triangle',
        info: 'bi-info-circle'
    };

    const toast = document.createElement('div');
    toast.className = `notification-toast fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3 z-50 animate-slide-in`;
    toast.innerHTML = `
        <i class="bi ${icons[type]} text-xl"></i>
        <span>${message}</span>
    `;

    document.body.appendChild(toast);

    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.classList.add('animate-slide-out');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// Helper functions for formatting
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slide-in {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slide-out {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .animate-slide-in {
        opacity: 0;
        transform: translateX(100%);
        animation: slide-in 0.3s ease-out forwards;
    }
    
    .animate-slide-out {
        animation: slide-out 0.3s ease-in forwards;
    }
`;
document.head.appendChild(style);

console.log('Documents JS Loaded');

