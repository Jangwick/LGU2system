/**
 * VDMsystem - Main JavaScript
 * Core functionality and utilities
 */

// =====================
// Global Configuration
// =====================
const VDM = {
    config: {
        baseUrl: window.location.origin + '/LGU2system/VDMsystem',
        apiUrl: window.location.origin + '/LGU2system/VDMsystem/api',
        refreshInterval: 30000, // 30 seconds
        toastDuration: 5000
    },
    
    // =====================
    // Utility Functions
    // =====================
    utils: {
        // Format date
        formatDate: function(dateString, format = 'short') {
            const date = new Date(dateString);
            const options = format === 'long' 
                ? { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }
                : { year: 'numeric', month: 'short', day: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        },
        
        // Format number
        formatNumber: function(num) {
            return new Intl.NumberFormat().format(num);
        },
        
        // Debounce function
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        // Get query parameter
        getQueryParam: function(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        },
        
        // Set query parameter
        setQueryParam: function(name, value) {
            const url = new URL(window.location.href);
            url.searchParams.set(name, value);
            window.history.pushState({}, '', url);
        }
    },
    
    // =====================
    // Toast Notifications
    // =====================
    toast: {
        container: null,
        
        init: function() {
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.id = 'toast-container';
                this.container.className = 'fixed top-4 right-4 z-50 flex flex-col gap-2';
                document.body.appendChild(this.container);
            }
        },
        
        show: function(message, type = 'info') {
            this.init();
            
            const icons = {
                success: 'bi-check-circle-fill',
                error: 'bi-exclamation-circle-fill',
                warning: 'bi-exclamation-triangle-fill',
                info: 'bi-info-circle-fill'
            };
            
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-red-500'
            };
            
            const toast = document.createElement('div');
            toast.className = `${colors[type]} text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 animate-slide-in-right`;
            toast.innerHTML = `
                <i class="bi ${icons[type]}"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.remove()" class="ml-2 hover:opacity-75">
                    <i class="bi bi-x"></i>
                </button>
            `;
            
            this.container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('animate-fade-out');
                setTimeout(() => toast.remove(), 300);
            }, VDM.config.toastDuration);
        },
        
        success: function(message) { this.show(message, 'success'); },
        error: function(message) { this.show(message, 'error'); },
        warning: function(message) { this.show(message, 'warning'); },
        info: function(message) { this.show(message, 'info'); }
    },
    
    // =====================
    // Sidebar Toggle
    // =====================
    sidebar: {
        isOpen: true,
        
        toggle: function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (window.innerWidth < 768) {
                // Mobile behavior
                sidebar.classList.toggle('open');
                if (overlay) overlay.classList.toggle('show');
            } else {
                // Desktop behavior
                sidebar.classList.toggle('sidebar-collapsed');
                this.isOpen = !sidebar.classList.contains('sidebar-collapsed');
                localStorage.setItem('sidebar-collapsed', !this.isOpen);
            }
        },
        
        init: function() {
            const collapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            const sidebar = document.getElementById('sidebar');
            
            if (collapsed && sidebar && window.innerWidth >= 768) {
                sidebar.classList.add('sidebar-collapsed');
                this.isOpen = false;
            }
        }
    },
    
    // =====================
    // Modal Functions
    // =====================
    modal: {
        show: function(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        },
        
        hide: function(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        },
        
        confirm: function(message, onConfirm, onCancel) {
            // Create confirm modal dynamically
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/50';
            modal.innerHTML = `
                <div class="bg-white rounded-lg shadow-xl p-6 max-w-md mx-4">
                    <div class="text-center mb-6">
                        <i class="bi bi-question-circle text-5xl text-red-500 mb-4"></i>
                        <p class="text-gray-700">${message}</p>
                    </div>
                    <div class="flex gap-3 justify-center">
                        <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300" id="modal-cancel">
                            Cancel
                        </button>
                        <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700" id="modal-confirm">
                            Confirm
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            document.getElementById('modal-confirm').onclick = () => {
                modal.remove();
                if (onConfirm) onConfirm();
            };
            
            document.getElementById('modal-cancel').onclick = () => {
                modal.remove();
                if (onCancel) onCancel();
            };
        }
    },
    
    // =====================
    // API Functions
    // =====================
    api: {
        fetch: async function(endpoint, options = {}) {
            try {
                const response = await fetch(VDM.config.apiUrl + endpoint, {
                    headers: {
                        'Content-Type': 'application/json',
                        ...options.headers
                    },
                    ...options
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'API request failed');
                }
                
                return data;
            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        },
        
        get: function(endpoint) {
            return this.fetch(endpoint, { method: 'GET' });
        },
        
        post: function(endpoint, body) {
            return this.fetch(endpoint, {
                method: 'POST',
                body: JSON.stringify(body)
            });
        },
        
        put: function(endpoint, body) {
            return this.fetch(endpoint, {
                method: 'PUT',
                body: JSON.stringify(body)
            });
        },
        
        delete: function(endpoint) {
            return this.fetch(endpoint, { method: 'DELETE' });
        }
    },
    
    // =====================
    // Voting Functions
    // =====================
    voting: {
        castVote: async function(sessionId, documentId, vote, remarks = '') {
            try {
                const result = await VDM.api.post('/voting/vote', {
                    session_id: sessionId,
                    document_id: documentId,
                    vote: vote,
                    remarks: remarks
                });
                
                VDM.toast.success('Vote recorded successfully!');
                return result;
            } catch (error) {
                VDM.toast.error('Failed to cast vote: ' + error.message);
                throw error;
            }
        },
        
        refreshResults: async function(sessionId) {
            try {
                return await VDM.api.get(`/voting/results/${sessionId}`);
            } catch (error) {
                console.error('Failed to refresh results:', error);
                throw error;
            }
        }
    },
    
    // =====================
    // Initialize
    // =====================
    init: function() {
        // Initialize sidebar
        this.sidebar.init();
        
        // Initialize tooltips
        document.querySelectorAll('[data-tooltip]').forEach(el => {
            el.classList.add('tooltip');
        });
        
        // Handle mobile sidebar overlay click
        const overlay = document.getElementById('sidebar-overlay');
        if (overlay) {
            overlay.addEventListener('click', () => {
                this.sidebar.toggle();
            });
        }
        
        // Initialize search with debounce
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', this.utils.debounce(function(e) {
                // Auto-submit search after typing stops
                if (e.target.form && e.target.value.length >= 3) {
                    e.target.form.submit();
                }
            }, 500));
        }
        
        console.log('VDMsystem initialized');
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    VDM.init();
});

// Export for global access
window.VDM = VDM;
