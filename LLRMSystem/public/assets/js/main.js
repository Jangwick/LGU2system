/**
 * Main JavaScript file for LRMS
 * Handles global functionality, utilities, and common features
 */

// Button Ripple Effect
function createRipple(event) {
    const button = event.currentTarget;
    const circle = document.createElement("span");
    const diameter = Math.max(button.clientWidth, button.clientHeight);
    const radius = diameter / 2;

    circle.style.width = circle.style.height = `${diameter}px`;
    circle.style.left = `${event.clientX - button.getBoundingClientRect().left - radius}px`;
    circle.style.top = `${event.clientY - button.getBoundingClientRect().top - radius}px`;
    circle.classList.add("ripple");

    const ripple = button.getElementsByClassName("ripple")[0];
    if (ripple) {
        ripple.remove();
    }

    button.appendChild(circle);
}

// Button Loading State
function setButtonLoading(button, isLoading, originalText = '') {
    if (isLoading) {
        button.dataset.originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="bi bi-arrow-repeat animate-spin mr-2"></i>Loading...';
        button.classList.add('opacity-75', 'cursor-not-allowed');
    } else {
        button.disabled = false;
        button.innerHTML = button.dataset.originalText || originalText;
        button.classList.remove('opacity-75', 'cursor-not-allowed');
    }
}

// Apply ripple effect to all buttons
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('button:not(.no-ripple)');
    buttons.forEach(button => {
        button.addEventListener('click', createRipple);
    });
});

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

    // Initialize drag-to-scroll for all .drag-scroll elements
    initDragScroll();
});

/**
 * Drag-to-Scroll Functionality
 * Allows users to click and drag to scroll content horizontally and vertically
 */
function initDragScroll() {
    const dragScrollContainers = document.querySelectorAll('.drag-scroll');
    
    dragScrollContainers.forEach(container => {
        let isDown = false;
        let startX;
        let startY;
        let scrollLeft;
        let scrollTop;
        let velX = 0;
        let velY = 0;
        let momentumID;

        container.addEventListener('mousedown', (e) => {
            // Don't initiate drag on buttons, links, inputs
            if (e.target.closest('button, a, input, select, textarea')) {
                return;
            }
            
            isDown = true;
            container.classList.add('dragging');
            startX = e.pageX - container.offsetLeft;
            startY = e.pageY - container.offsetTop;
            scrollLeft = container.scrollLeft;
            scrollTop = container.scrollTop;
            cancelMomentum();
        });

        container.addEventListener('mouseleave', () => {
            if (isDown) {
                isDown = false;
                container.classList.remove('dragging');
                startMomentum();
            }
        });

        container.addEventListener('mouseup', () => {
            if (isDown) {
                isDown = false;
                container.classList.remove('dragging');
                startMomentum();
            }
        });

        container.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            
            const x = e.pageX - container.offsetLeft;
            const y = e.pageY - container.offsetTop;
            const walkX = (x - startX) * 1.5; // Scroll speed multiplier
            const walkY = (y - startY) * 1.5;
            
            velX = container.scrollLeft - (scrollLeft - walkX);
            velY = container.scrollTop - (scrollTop - walkY);
            
            container.scrollLeft = scrollLeft - walkX;
            container.scrollTop = scrollTop - walkY;
        });

        // Touch support for mobile
        container.addEventListener('touchstart', (e) => {
            if (e.target.closest('button, a, input, select, textarea')) {
                return;
            }
            
            isDown = true;
            container.classList.add('dragging');
            startX = e.touches[0].pageX - container.offsetLeft;
            startY = e.touches[0].pageY - container.offsetTop;
            scrollLeft = container.scrollLeft;
            scrollTop = container.scrollTop;
            cancelMomentum();
        }, { passive: true });

        container.addEventListener('touchend', () => {
            isDown = false;
            container.classList.remove('dragging');
            startMomentum();
        });

        container.addEventListener('touchmove', (e) => {
            if (!isDown) return;
            
            const x = e.touches[0].pageX - container.offsetLeft;
            const y = e.touches[0].pageY - container.offsetTop;
            const walkX = (x - startX) * 1.5;
            const walkY = (y - startY) * 1.5;
            
            velX = container.scrollLeft - (scrollLeft - walkX);
            velY = container.scrollTop - (scrollTop - walkY);
            
            container.scrollLeft = scrollLeft - walkX;
            container.scrollTop = scrollTop - walkY;
        }, { passive: true });

        // Momentum scrolling
        function startMomentum() {
            cancelMomentum();
            momentumID = requestAnimationFrame(momentumLoop);
        }

        function cancelMomentum() {
            cancelAnimationFrame(momentumID);
        }

        function momentumLoop() {
            container.scrollLeft += velX;
            container.scrollTop += velY;
            velX *= 0.95; // Friction
            velY *= 0.95;
            
            if (Math.abs(velX) > 0.5 || Math.abs(velY) > 0.5) {
                momentumID = requestAnimationFrame(momentumLoop);
            }
        }
    });
}

// Re-initialize drag scroll when new content is loaded (for AJAX)
window.initDragScroll = initDragScroll;

console.log('LRMS Main JS Loaded');
