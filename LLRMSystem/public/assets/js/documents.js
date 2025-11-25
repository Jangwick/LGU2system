/**
 * Document Management JavaScript
 * Handles document CRUD operations, filtering, and interactions
 */

// Global variables
let selectedDocuments = [];

// Document Table Interactions
class DocumentManager {
    constructor() {
        this.selectedDocuments = new Set();
        this.init();
    }
    
    init() {
        this.attachEventListeners();
        this.initializeFilters();
        this.initializeAdvancedFilters();
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
        const bulkDownloadBtn = document.querySelector('button:has(.bi-download)');
        if (bulkDownloadBtn && bulkDownloadBtn.textContent.includes('Bulk')) {
            bulkDownloadBtn.addEventListener('click', () => this.bulkDownload());
        }
        
        const bulkDeleteBtn = document.querySelectorAll('button:has(.bi-trash)');
        bulkDeleteBtn.forEach(btn => {
            if (btn.textContent.includes('Delete Selected')) {
                btn.addEventListener('click', () => this.bulkDelete());
            }
        });
    }
    
    initializeFilters() {
        const searchInput = document.querySelector('input[placeholder*="Search"]');
        const typeFilter = document.querySelector('select');
        const statusFilter = document.querySelectorAll('select')[1];
        
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    applyFilters();
                }, 500);
            });
        }
        
        if (typeFilter) {
            typeFilter.addEventListener('change', applyFilters);
        }
        
        if (statusFilter) {
            statusFilter.addEventListener('change', applyFilters);
        }
    }
    
    initializeAdvancedFilters() {
        const advancedFiltersBtn = document.querySelector('button:has(.bi-funnel)');
        
        if (advancedFiltersBtn) {
            advancedFiltersBtn.addEventListener('click', () => this.toggleAdvancedFilters());
        }
    }
    
    toggleAdvancedFilters() {
        let advancedPanel = document.getElementById('advanced-filters-panel');
        
        if (!advancedPanel) {
            advancedPanel = this.createAdvancedFiltersPanel();
            const filtersSection = document.querySelector('.bg-white.rounded-xl.shadow-md.p-6.mb-6:nth-child(2)');
            if (filtersSection) {
                filtersSection.appendChild(advancedPanel);
            }
        }
        
        advancedPanel.classList.toggle('hidden');
    }
    
    createAdvancedFiltersPanel() {
        const panel = document.createElement('div');
        panel.id = 'advanced-filters-panel';
        panel.className = 'mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200 hidden';
        panel.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" name="date_from" class="input-field" id="filter-date-from">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" name="date_to" class="input-field" id="filter-date-to">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">File Size</label>
                    <select name="file_size" class="input-field" id="filter-file-size">
                        <option value="">Any Size</option>
                        <option value="small">Small (< 1MB)</option>
                        <option value="medium">Medium (1-10MB)</option>
                        <option value="large">Large (> 10MB)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                    <input type="text" name="tags" placeholder="Enter tags..." class="input-field" id="filter-tags">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select name="category" class="input-field" id="filter-category">
                        <option value="">All Categories</option>
                        <option value="legislative">Legislative</option>
                        <option value="administrative">Administrative</option>
                        <option value="financial">Financial</option>
                        <option value="legal">Legal</option>
                        <option value="public-service">Public Service</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                    <input type="text" name="reference" placeholder="Search by reference..." class="input-field" id="filter-reference">
                </div>
            </div>
            
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="clearAdvancedFilters()" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    <i class="bi bi-x-circle mr-1"></i>Clear Filters
                </button>
                <button type="button" onclick="applyAdvancedFilters()" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    <i class="bi bi-search mr-1"></i>Apply Filters
                </button>
            </div>
        `;
        
        return panel;
    }
    
    selectAll(checked) {
        document.querySelectorAll('table tbody input[type="checkbox"]').forEach(checkbox => {
            checkbox.checked = checked;
            this.toggleSelection(checkbox);
        });
        this.updateBulkActionsVisibility();
    }
    
    toggleSelection(checkbox) {
        const documentId = checkbox.value;
        if (checkbox.checked) {
            this.selectedDocuments.add(documentId);
            selectedDocuments.push(documentId);
            checkbox.closest('tr')?.classList.add('bg-blue-50');
        } else {
            this.selectedDocuments.delete(documentId);
            selectedDocuments = selectedDocuments.filter(id => id !== documentId);
            checkbox.closest('tr')?.classList.remove('bg-blue-50');
        }
        this.updateBulkActionsVisibility();
    }
    
    updateBulkActionsVisibility() {
        const bulkDownloadBtn = document.querySelector('button:has(.bi-download)');
        const bulkDeleteBtn = document.querySelectorAll('button:has(.bi-trash)');
        
        const hasSelected = this.selectedDocuments.size > 0;
        
        if (bulkDownloadBtn && bulkDownloadBtn.textContent.includes('Bulk')) {
            bulkDownloadBtn.disabled = !hasSelected;
            bulkDownloadBtn.classList.toggle('opacity-50', !hasSelected);
            bulkDownloadBtn.classList.toggle('cursor-not-allowed', !hasSelected);
        }
        
        bulkDeleteBtn.forEach(btn => {
            if (btn.textContent.includes('Delete Selected')) {
                btn.disabled = !hasSelected;
                btn.classList.toggle('opacity-50', !hasSelected);
                btn.classList.toggle('cursor-not-allowed', !hasSelected);
            }
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
const docManager = new DocumentManager();

// Apply basic filters
function applyFilters() {
    const searchInput = document.querySelector('input[placeholder*="Search"]');
    const typeFilter = document.querySelector('select');
    const statusFilter = document.querySelectorAll('select')[1];
    
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
    
    // Reload page with filters
    window.location.href = window.location.pathname + '?' + params.toString();
}

// Apply advanced filters
function applyAdvancedFilters() {
    const panel = document.getElementById('advanced-filters-panel');
    if (!panel) return;
    
    const params = new URLSearchParams(window.location.search);
    
    const dateFrom = document.getElementById('filter-date-from');
    const dateTo = document.getElementById('filter-date-to');
    const fileSize = document.getElementById('filter-file-size');
    const tags = document.getElementById('filter-tags');
    const category = document.getElementById('filter-category');
    const reference = document.getElementById('filter-reference');
    
    if (dateFrom && dateFrom.value) params.set('date_from', dateFrom.value);
    else params.delete('date_from');
    
    if (dateTo && dateTo.value) params.set('date_to', dateTo.value);
    else params.delete('date_to');
    
    if (fileSize && fileSize.value) params.set('file_size', fileSize.value);
    else params.delete('file_size');
    
    if (tags && tags.value) params.set('tags', tags.value);
    else params.delete('tags');
    
    if (category && category.value) params.set('category', category.value);
    else params.delete('category');
    
    if (reference && reference.value) params.set('reference', reference.value);
    else params.delete('reference');
    
    window.location.href = window.location.pathname + '?' + params.toString();
}

// Clear advanced filters
function clearAdvancedFilters() {
    const panel = document.getElementById('advanced-filters-panel');
    if (!panel) return;
    
    const inputs = panel.querySelectorAll('input, select');
    inputs.forEach(input => {
        if (input.type === 'date' || input.type === 'text') {
            input.value = '';
        } else if (input.tagName === 'SELECT') {
            input.selectedIndex = 0;
        }
    });
}

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
        animation: slide-in 0.3s ease-out;
    }
    
    .animate-slide-out {
        animation: slide-out 0.3s ease-in;
    }
`;
document.head.appendChild(style);

console.log('Documents JS Loaded');

