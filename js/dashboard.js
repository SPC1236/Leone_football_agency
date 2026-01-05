// js/dashboard.js - Dashboard JavaScript File
// Handles dashboard-specific functionality for all user types

'use strict';

// ============================================================================
// SIDEBAR TOGGLE (MOBILE)
// ============================================================================

/**
 * Toggle sidebar visibility on mobile
 */
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (sidebar) {
        sidebar.classList.toggle('active');
        
        // Create overlay if it doesn't exist
        if (!overlay && sidebar.classList.contains('active')) {
            const newOverlay = document.createElement('div');
            newOverlay.className = 'sidebar-overlay';
            newOverlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 999;
                display: none;
            `;
            document.body.appendChild(newOverlay);
            
            // Close sidebar when clicking overlay
            newOverlay.addEventListener('click', closeSidebar);
            
            // Show overlay
            setTimeout(() => {
                newOverlay.style.display = 'block';
            }, 10);
        } else if (overlay) {
            if (sidebar.classList.contains('active')) {
                overlay.style.display = 'block';
            } else {
                overlay.style.display = 'none';
            }
        }
    }
}

/**
 * Close sidebar
 */
function closeSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (sidebar) {
        sidebar.classList.remove('active');
    }
    
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// ============================================================================
// TABLE SORTING
// ============================================================================

/**
 * Make tables sortable by clicking headers
 */
function initTableSorting() {
    const tables = document.querySelectorAll('table[data-sortable]');
    
    tables.forEach(table => {
        const headers = table.querySelectorAll('th');
        
        headers.forEach((header, index) => {
            header.style.cursor = 'pointer';
            header.style.position = 'relative';
            header.style.userSelect = 'none';
            
            // Add sort indicator
            const indicator = document.createElement('span');
            indicator.className = 'sort-indicator';
            indicator.innerHTML = ' ↕';
            indicator.style.fontSize = '0.8rem';
            indicator.style.marginLeft = '5px';
            indicator.style.opacity = '0.5';
            header.appendChild(indicator);
            
            header.addEventListener('click', function() {
                sortTable(table, index);
            });
        });
    });
}

/**
 * Sort table by column
 */
function sortTable(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const header = table.querySelectorAll('th')[columnIndex];
    const isAscending = header.classList.contains('sort-asc');
    
    // Remove sort classes from all headers
    table.querySelectorAll('th').forEach(th => {
        th.classList.remove('sort-asc', 'sort-desc');
        const indicator = th.querySelector('.sort-indicator');
        if (indicator) indicator.innerHTML = ' ↕';
    });
    
    // Sort rows
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();
        
        // Try to parse as number
        const aNum = parseFloat(aValue.replace(/[^0-9.-]/g, ''));
        const bNum = parseFloat(bValue.replace(/[^0-9.-]/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return isAscending ? bNum - aNum : aNum - bNum;
        }
        
        // String comparison
        return isAscending 
            ? bValue.localeCompare(aValue)
            : aValue.localeCompare(bValue);
    });
    
    // Update table
    rows.forEach(row => tbody.appendChild(row));
    
    // Update header
    const indicator = header.querySelector('.sort-indicator');
    if (isAscending) {
        header.classList.add('sort-desc');
        indicator.innerHTML = ' ↓';
    } else {
        header.classList.add('sort-asc');
        indicator.innerHTML = ' ↑';
    }
}

// ============================================================================
// TABLE SEARCH/FILTER
// ============================================================================

/**
 * Add search functionality to tables
 */
function initTableSearch() {
    const searchInputs = document.querySelectorAll('[data-table-search]');
    
    searchInputs.forEach(input => {
        const tableId = input.getAttribute('data-table-search');
        const table = document.getElementById(tableId);
        
        if (table) {
            input.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
                
                // Show "no results" message
                const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
                let noResultsRow = table.querySelector('.no-results-row');
                
                if (visibleRows.length === 0 && searchTerm) {
                    if (!noResultsRow) {
                        noResultsRow = document.createElement('tr');
                        noResultsRow.className = 'no-results-row';
                        noResultsRow.innerHTML = `<td colspan="100" style="text-align: center; padding: 20px; color: #6c757d;">No results found</td>`;
                        table.querySelector('tbody').appendChild(noResultsRow);
                    }
                    noResultsRow.style.display = '';
                } else if (noResultsRow) {
                    noResultsRow.style.display = 'none';
                }
            });
        }
    });
}

// ============================================================================
// STAT CARDS ANIMATION
// ============================================================================

/**
 * Animate numbers in stat cards on page load
 */
function animateStatNumbers() {
    const statNumbers = document.querySelectorAll('.stat-card .number');
    
    statNumbers.forEach(element => {
        const target = parseFloat(element.textContent.replace(/[^0-9.-]/g, ''));
        
        if (!isNaN(target)) {
            let current = 0;
            const increment = target / 50;
            const suffix = element.textContent.replace(/[0-9.-]/g, '');
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target + suffix;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current) + suffix;
                }
            }, 20);
        }
    });
}

// ============================================================================
// CHARTS (Simple Bar Charts)
// ============================================================================

/**
 * Create simple bar charts from data
 */
function initSimpleCharts() {
    const charts = document.querySelectorAll('[data-chart]');
    
    charts.forEach(chart => {
        const data = JSON.parse(chart.getAttribute('data-chart'));
        createBarChart(chart, data);
    });
}

/**
 * Create a bar chart
 */
function createBarChart(container, data) {
    const maxValue = Math.max(...data.map(item => item.value));
    
    container.innerHTML = '';
    container.style.display = 'flex';
    container.style.flexDirection = 'column';
    container.style.gap = '10px';
    
    data.forEach(item => {
        const barWrapper = document.createElement('div');
        barWrapper.style.display = 'flex';
        barWrapper.style.alignItems = 'center';
        barWrapper.style.gap = '10px';
        
        const label = document.createElement('div');
        label.textContent = item.label;
        label.style.minWidth = '100px';
        label.style.fontWeight = '600';
        
        const barContainer = document.createElement('div');
        barContainer.style.flex = '1';
        barContainer.style.backgroundColor = '#f0f0f0';
        barContainer.style.borderRadius = '4px';
        barContainer.style.height = '30px';
        barContainer.style.position = 'relative';
        barContainer.style.overflow = 'hidden';
        
        const bar = document.createElement('div');
        bar.style.height = '100%';
        bar.style.background = 'linear-gradient(90deg, var(--accent-green), var(--light-green))';
        bar.style.borderRadius = '4px';
        bar.style.transition = 'width 1s ease';
        bar.style.display = 'flex';
        bar.style.alignItems = 'center';
        bar.style.justifyContent = 'center';
        bar.style.color = 'white';
        bar.style.fontWeight = 'bold';
        bar.style.fontSize = '0.9rem';
        bar.textContent = item.value;
        
        const percentage = (item.value / maxValue) * 100;
        setTimeout(() => {
            bar.style.width = percentage + '%';
        }, 100);
        
        barContainer.appendChild(bar);
        barWrapper.appendChild(label);
        barWrapper.appendChild(barContainer);
        container.appendChild(barWrapper);
    });
}

// ============================================================================
// MODAL DIALOGS
// ============================================================================

/**
 * Open modal dialog
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Close modal dialog
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

/**
 * Initialize modal close buttons
 */
function initModals() {
    // Close on outside click
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
    
    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            modals.forEach(modal => {
                if (modal.classList.contains('active')) {
                    closeModal(modal.id);
                }
            });
        }
    });
}

// ============================================================================
// TABS
// ============================================================================

/**
 * Initialize tab navigation
 */
function initTabs() {
    const tabContainers = document.querySelectorAll('[data-tabs]');
    
    tabContainers.forEach(container => {
        const buttons = container.querySelectorAll('[data-tab]');
        
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                const tabContent = document.getElementById(tabId);
                
                // Remove active class from all buttons and contents
                buttons.forEach(btn => btn.classList.remove('active'));
                container.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                    content.style.display = 'none';
                });
                
                // Add active class to current
                this.classList.add('active');
                if (tabContent) {
                    tabContent.classList.add('active');
                    tabContent.style.display = 'block';
                }
            });
        });
    });
}

// ============================================================================
// FILE UPLOAD PREVIEW
// ============================================================================

/**
 * Show preview of uploaded images
 */
function initFileUploadPreview() {
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const previewId = this.getAttribute('data-preview');
            const preview = document.getElementById(previewId);
            const file = e.target.files[0];
            
            if (file && preview) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" style="max-width: 200px; max-height: 200px; border-radius: 8px;">`;
                };
                
                reader.readAsDataURL(file);
            }
        });
    });
}

// ============================================================================
// NOTIFICATIONS
// ============================================================================

/**
 * Show notification toast
 */
function showNotification(message, type = 'info', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background-color: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
        color: white;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, duration);
}

// ============================================================================
// DROPDOWN MENUS
// ============================================================================

/**
 * Initialize dropdown menus
 */
function initDropdowns() {
    const dropdowns = document.querySelectorAll('[data-dropdown]');
    
    dropdowns.forEach(dropdown => {
        const button = dropdown.querySelector('[data-dropdown-button]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');
        
        if (button && menu) {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                menu.classList.toggle('active');
            });
            
            // Close on outside click
            document.addEventListener('click', function() {
                menu.classList.remove('active');
            });
        }
    });
}

// ============================================================================
// AUTO-SAVE FORMS
// ============================================================================

/**
 * Auto-save form data to localStorage
 */
function initAutoSave() {
    const forms = document.querySelectorAll('form[data-autosave]');
    
    forms.forEach(form => {
        const formId = form.id || 'autosave-form';
        
        // Load saved data
        const savedData = localStorage.getItem(`autosave-${formId}`);
        if (savedData) {
            const data = JSON.parse(savedData);
            Object.keys(data).forEach(name => {
                const field = form.querySelector(`[name="${name}"]`);
                if (field && field.type !== 'password') {
                    field.value = data[name];
                }
            });
        }
        
        // Save on input
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('input', debounce(function() {
                const formData = {};
                inputs.forEach(field => {
                    if (field.type !== 'password') {
                        formData[field.name] = field.value;
                    }
                });
                localStorage.setItem(`autosave-${formId}`, JSON.stringify(formData));
            }, 1000));
        });
        
        // Clear on submit
        form.addEventListener('submit', function() {
            localStorage.removeItem(`autosave-${formId}`);
        });
    });
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Debounce function
 */
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

/**
 * Format number with commas
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Format date
 */
function formatDate(date, format = 'short') {
    const d = new Date(date);
    if (format === 'short') {
        return d.toLocaleDateString();
    } else if (format === 'long') {
        return d.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }
    return d.toISOString();
}

// ============================================================================
// INITIALIZATION
// ============================================================================

/**
 * Initialize all dashboard functions
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard - Initializing...');
    
    // Initialize features
    initTableSorting();
    initTableSearch();
    animateStatNumbers();
    initSimpleCharts();
    initModals();
    initTabs();
    initFileUploadPreview();
    initDropdowns();
    initAutoSave();
    
    // Close sidebar on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
    
    console.log('Dashboard - Ready!');
});

// ============================================================================
// GLOBAL FUNCTIONS (accessible from HTML)
// ============================================================================

// Make functions available globally
window.toggleSidebar = toggleSidebar;
window.closeSidebar = closeSidebar;
window.openModal = openModal;
window.closeModal = closeModal;
window.showNotification = showNotification;
window.formatNumber = formatNumber;
window.formatDate = formatDate;