/**
 * Reports & Analytics JavaScript
 * Handles interactive charts, filters, and report generation
 */

// Chart instances (shared with the view, which registers each chart here)
window.chartsInstances = window.chartsInstances || {};
const chartsInstances = window.chartsInstances;

// Date range filter
let dateRange = {
    start: null,
    end: null
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    initializeDateRangePicker();
    initializeExportFunctions();
    initializeRefreshButton();
    initializeFilterControls();
});

/**
 * Initialize all charts
 */
function initializeCharts() {
    // Charts are already initialized in PHP view
    // This function can be used for dynamic updates
}

/**
 * Initialize date range picker
 */
function initializeDateRangePicker() {
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    const applyRangeBtn = document.getElementById('apply-range');
    
    if (applyRangeBtn) {
        applyRangeBtn.addEventListener('click', function() {
            dateRange.start = startDateInput?.value;
            dateRange.end = endDateInput?.value;
            refreshDashboard();
        });
    }
}

/**
 * Initialize export functions
 */
function initializeExportFunctions() {
    const exportButtons = document.querySelectorAll('[data-export]');
    
    exportButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const reportType = this.dataset.export;
            exportReport(reportType);
        });
    });
}

/**
 * Initialize refresh button
 */
function initializeRefreshButton() {
    const refreshBtn = document.getElementById('refresh-dashboard');
    
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            refreshDashboard();
        });
    }
}

/**
 * Initialize filter controls
 */
function initializeFilterControls() {
    const filterSelects = document.querySelectorAll('[data-filter]');
    
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            applyFilters();
        });
    });
}

/**
 * Refresh dashboard data
 */
async function refreshDashboard() {
    showLoading(true);
    
    try {
        const params = new URLSearchParams();
        
        if (dateRange.start) params.append('start_date', dateRange.start);
        if (dateRange.end) params.append('end_date', dateRange.end);
        
        const response = await fetch(App.url(`modules/reports-analytics/controllers/ReportController.php?action=refresh&${params.toString()}`));
        const data = await response.json();
        
        if (data.success) {
            updateDashboard(data);
            showNotification('Dashboard refreshed successfully', 'success');
        } else {
            showNotification('Failed to refresh dashboard', 'error');
        }
    } catch (error) {
        console.error('Refresh error:', error);
        showNotification('An error occurred while refreshing', 'error');
    } finally {
        showLoading(false);
    }
}

/**
 * Update dashboard with new data
 */
function updateDashboard(data) {
    // Update key metrics
    if (data.stats) {
        updateStats(data.stats);
    }
    
    // Update charts
    if (data.charts) {
        updateCharts(data.charts);
    }
    
    // Update tables
    if (data.tables) {
        updateTables(data.tables);
    }
}

/**
 * Update statistics cards
 */
function updateStats(stats) {
    Object.keys(stats).forEach(key => {
        const element = document.querySelector(`[data-stat="${key}"]`);
        if (element) {
            element.textContent = formatNumber(stats[key]);
        }
    });
}

/**
 * Update charts with new data
 */
function updateCharts(charts) {
    Object.keys(charts).forEach(chartId => {
        const chart = chartsInstances[chartId];
        if (chart) {
            chart.data.datasets[0].data = charts[chartId].data;
            chart.data.labels = charts[chartId].labels;
            chart.update();
        }
    });
}

/**
 * Update tables with new data
 */
function updateTables(tables) {
    Object.keys(tables).forEach(tableId => {
        const table = document.getElementById(tableId);
        if (table) {
            const tbody = table.querySelector('tbody');
            if (tbody) {
                tbody.innerHTML = generateTableRows(tables[tableId]);
            }
        }
    });
}

/**
 * Export report
 */
function exportReport(reportType) {
    const params = new URLSearchParams();
    params.append('export', reportType);
    
    if (dateRange.start) params.append('start_date', dateRange.start);
    if (dateRange.end) params.append('end_date', dateRange.end);
    
    window.location.href = App.config.urls.reports + `/views/index.php?${params.toString()}`;
}

/**
 * Apply filters
 */
function applyFilters() {
    const filters = {};
    
    document.querySelectorAll('[data-filter]').forEach(element => {
        const filterName = element.dataset.filter;
        const filterValue = element.value;
        
        if (filterValue) {
            filters[filterName] = filterValue;
        }
    });
    
    // Reload with filters
    const params = new URLSearchParams(filters);
    window.location.href = `?${params.toString()}`;
}

/**
 * Show export modal
 */
function showExportModal() {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

/**
 * Close export modal
 */
function closeExportModal() {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

/**
 * Print report
 */
function printReport() {
    window.print();
}

/**
 * Generate custom report
 */
async function generateCustomReport() {
    const reportType = document.getElementById('report-type-select')?.value;
    const dateFrom = document.getElementById('date-from-input')?.value;
    const dateTo = document.getElementById('date-to-input')?.value;
    
    if (!reportType) {
        showNotification('Please select a report type', 'warning');
        return;
    }
    
    showLoading(true);
    
    try {
        const params = new URLSearchParams({
            type: reportType,
            start_date: dateFrom || '',
            end_date: dateTo || ''
        });
        
        const response = await fetch(App.apiUrl('reports', `generate-report.php?${params.toString()}`));
        const data = await response.json();
        
        if (data.success) {
            displayCustomReport(data.report);
            showNotification('Report generated successfully', 'success');
        } else {
            showNotification(data.error || 'Failed to generate report', 'error');
        }
    } catch (error) {
        console.error('Report generation error:', error);
        showNotification('An error occurred during report generation', 'error');
    } finally {
        showLoading(false);
    }
}

/**
 * Display custom report
 */
function displayCustomReport(report) {
    const container = document.getElementById('custom-report-container');
    if (!container) return;
    
    container.innerHTML = `
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-xl font-bold mb-4">${report.title}</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            ${report.headers.map(header => `
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">${header}</th>
                            `).join('')}
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${report.data.map(row => `
                            <tr>
                                ${row.map(cell => `
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${cell}</td>
                                `).join('')}
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

/**
 * Download chart as image
 */
function downloadChartImage(chartId) {
    const chart = chartsInstances[chartId];
    if (!chart) return;
    
    const url = chart.toBase64Image();
    const link = document.createElement('a');
    link.download = `${chartId}_${Date.now()}.png`;
    link.href = url;
    link.click();
}

/**
 * Toggle chart type
 */
function toggleChartType(chartId, newType) {
    const chart = chartsInstances[chartId];
    if (!chart) return;
    
    chart.config.type = newType;
    chart.update();
}

/**
 * Show loading indicator
 */
function showLoading(show) {
    const loader = document.getElementById('loading-indicator');
    if (loader) {
        loader.classList.toggle('hidden', !show);
    }
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
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
    toast.className = `notification-toast fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3 z-50`;
    toast.innerHTML = `
        <i class="bi ${icons[type]} text-xl"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('opacity-0', 'transition-opacity');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

/**
 * Format number with commas
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Generate table rows HTML
 */
function generateTableRows(data) {
    return data.map(row => {
        return `
            <tr>
                ${Object.values(row).map(cell => `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${cell}</td>
                `).join('')}
            </tr>
        `;
    }).join('');
}

/**
 * Schedule automated report
 */
async function scheduleReport() {
    const reportType = prompt('Enter report type:');
    const frequency = prompt('Enter frequency (daily/weekly/monthly):');
    
    if (!reportType || !frequency) return;
    
    try {
        const response = await fetch(App.apiUrl('reports', 'schedule-report.php'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ report_type: reportType, frequency })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Report scheduled successfully', 'success');
        } else {
            showNotification(data.error || 'Failed to schedule report', 'error');
        }
    } catch (error) {
        console.error('Schedule error:', error);
        showNotification('An error occurred', 'error');
    }
}

console.log('Reports & Analytics JS Loaded');
