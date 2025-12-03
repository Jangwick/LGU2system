/**
 * Main JavaScript file for LRMS
 * Handles global functionality, utilities, and common features
 */

// Toast Notification System
class ToastNotification {
    constructor() {
        this.container = document.getElementById('toast-container');
    }
    
    show(message, type = 'success', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `toast-${type} animate-slide-in`;
        
        const icons = {
            success: 'check-circle-fill',
            error: 'x-circle-fill',
            warning: 'exclamation-triangle-fill',
            info: 'info-circle-fill'
        };
        
        const colors = {
            success: 'bg-green-50 border-green-200 text-green-800',
            error: 'bg-red-50 border-red-200 text-red-800',
            warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
            info: 'bg-blue-50 border-blue-200 text-blue-800'
        };
        
        toast.innerHTML = `
            <div class="flex items-center p-4 border rounded-lg shadow-lg ${colors[type]} min-w-[300px] max-w-md">
                <i class="bi bi-${icons[type]} mr-3 text-xl"></i>
                <span class="flex-1">${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-3 hover:opacity-70">
                    <i class="bi bi-x text-xl"></i>
                </button>
            </div>
        `;
        
        this.container.appendChild(toast);
        
        // Auto remove after duration
        setTimeout(() => {
            toast.classList.add('animate-fade-out');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
}

// Initialize toast notifications
const toast = new ToastNotification();

// Confirm Dialog
function confirmDialog(message, onConfirm) {
    if (confirm(message)) {
        onConfirm();
    }
}

// Format Date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Format File Size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Copy to Clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        toast.show('Copied to clipboard!', 'success', 2000);
    }).catch(() => {
        toast.show('Failed to copy', 'error', 2000);
    });
}

// Debounce Function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Quick Search Functionality
const quickSearch = document.getElementById('quick-search');
if (quickSearch) {
    quickSearch.addEventListener('keyup', debounce(function(e) {
        const query = e.target.value.trim();
        if (query.length > 2) {
            // Perform search (API call would go here)
            console.log('Searching for:', query);
        }
    }, 500));
    
    quickSearch.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = e.target.value.trim();
            if (query.length > 0) {
                window.location.href = `/modules/search/views/index.php?q=${encodeURIComponent(query)}`;
            }
        }
    });
}

// Table Row Selection
document.querySelectorAll('table input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const row = this.closest('tr');
        if (this.checked) {
            row.classList.add('bg-blue-50');
        } else {
            row.classList.remove('bg-blue-50');
        }
    });
});

// Select All Checkboxes
document.querySelectorAll('[data-select-all]').forEach(selectAll => {
    selectAll.addEventListener('change', function() {
        const target = this.getAttribute('data-target');
        document.querySelectorAll(target).forEach(checkbox => {
            checkbox.checked = this.checked;
            checkbox.dispatchEvent(new Event('change'));
        });
    });
});

// Loading State Helper
function setLoadingState(button, isLoading) {
    if (isLoading) {
        button.disabled = true;
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = '<i class="bi bi-hourglass-split mr-2 animate-spin"></i>Loading...';
    } else {
        button.disabled = false;
        button.innerHTML = button.dataset.originalText;
    }
}

// Form Validation Helper
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('border-red-500');
            const errorId = input.id + '-error';
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.textContent = 'This field is required';
                errorElement.classList.remove('hidden');
            }
        } else {
            input.classList.remove('border-red-500');
            const errorId = input.id + '-error';
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.classList.add('hidden');
            }
        }
    });
    
    return isValid;
}

// Auto-hide alerts
document.querySelectorAll('[data-auto-hide]').forEach(alert => {
    const duration = parseInt(alert.getAttribute('data-auto-hide')) || 5000;
    setTimeout(() => {
        alert.classList.add('animate-fade-out');
        setTimeout(() => alert.remove(), 300);
    }, duration);
});

// Print Page
function printPage() {
    window.print();
}

// Export Table to CSV
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(col => rowData.push(col.textContent.trim()));
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('href', url);
    a.setAttribute('download', filename);
    a.click();
    
    toast.show('Table exported successfully!', 'success');
}

// Initialize tooltips (if Bootstrap is used)
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el);
});

// Session timeout warning
let sessionTimeout;
const SESSION_DURATION = 30 * 60 * 1000; // 30 minutes

function resetSessionTimeout() {
    clearTimeout(sessionTimeout);
    sessionTimeout = setTimeout(() => {
        toast.show('Your session is about to expire. Please save your work.', 'warning', 10000);
        setTimeout(() => {
            window.location.href = '/modules/authentication/controllers/LogoutController.php';
        }, 60000); // Logout after 1 minute warning
    }, SESSION_DURATION);
}

// Reset timeout on user activity
['mousedown', 'keydown', 'scroll', 'touchstart'].forEach(event => {
    document.addEventListener(event, resetSessionTimeout);
});

// Initialize session timeout
resetSessionTimeout();

// ========================================
// Sidebar Tooltip Functionality (for collapsed state)
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');

    // Add data-tooltip attributes to nav items for collapsed state
    function addTooltipsToNavItems() {
        if (sidebar) {
            const navItems = sidebar.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                const span = item.querySelector('span');
                if (span && !item.getAttribute('data-tooltip')) {
                    item.setAttribute('data-tooltip', span.textContent.trim());
                }
            });
        }
    }
    addTooltipsToNavItems();

    // Mobile sidebar functionality
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileSidebar = document.getElementById('mobile-sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const closeMobileSidebar = document.getElementById('close-mobile-sidebar');

    if (mobileMenuBtn && mobileSidebar) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileSidebar.classList.remove('-translate-x-full');
            sidebarOverlay?.classList.remove('hidden');
        });
    }

    if (closeMobileSidebar && mobileSidebar) {
        closeMobileSidebar.addEventListener('click', function() {
            mobileSidebar.classList.add('-translate-x-full');
            sidebarOverlay?.classList.add('hidden');
        });
    }

    if (sidebarOverlay && mobileSidebar) {
        sidebarOverlay.addEventListener('click', function() {
            mobileSidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });
    }
});

console.log('LRMS Main JS Loaded');
