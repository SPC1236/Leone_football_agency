// js/main.js - Main JavaScript File for Freetown Football Agency
// Handles general site-wide functionality

'use strict';

// ============================================================================
// MOBILE NAVIGATION
// ============================================================================

/**
 * Toggle mobile navigation menu
 */
function initMobileMenu() {
    const mobileMenuBtn = document.querySelector('.mobile-menu');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuBtn && navLinks) {
        mobileMenuBtn.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            this.classList.toggle('active');
            
            // Change icon
            if (this.classList.contains('active')) {
                this.textContent = '✕';
            } else {
                this.textContent = '☰';
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.header-container')) {
                navLinks.classList.remove('active');
                mobileMenuBtn.classList.remove('active');
                mobileMenuBtn.textContent = '☰';
            }
        });
        
        // Close menu on link click (mobile)
        const links = navLinks.querySelectorAll('a');
        links.forEach(link => {
            link.addEventListener('click', function() {
                navLinks.classList.remove('active');
                mobileMenuBtn.classList.remove('active');
                mobileMenuBtn.textContent = '☰';
            });
        });
    }
}

// ============================================================================
// SMOOTH SCROLLING
// ============================================================================

/**
 * Smooth scroll to anchor links
 */
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Ignore # only links
            if (href === '#') return;
            
            const target = document.querySelector(href);
            
            if (target) {
                e.preventDefault();
                
                const headerOffset = 80;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// ============================================================================
// STICKY HEADER
// ============================================================================

/**
 * Add shadow to header on scroll
 */
function initStickyHeader() {
    const header = document.querySelector('header');
    
    if (header) {
        let lastScroll = 0;
        
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            lastScroll = currentScroll;
        });
    }
}

// ============================================================================
// SCROLL ANIMATIONS
// ============================================================================

/**
 * Animate elements on scroll into view
 */
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    const animateElements = document.querySelectorAll('.stat-item, .talent-card, .service-card, .testimonial, .team-member');
    
    animateElements.forEach(element => {
        element.classList.add('animate-ready');
        observer.observe(element);
    });
}

// ============================================================================
// FORM VALIDATION
// ============================================================================

/**
 * Enhanced form validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                // Remove previous error
                const errorMsg = field.parentElement.querySelector('.error-message');
                if (errorMsg) errorMsg.remove();
                field.classList.remove('error');
                
                // Check if empty
                if (!field.value.trim()) {
                    isValid = false;
                    showFieldError(field, 'This field is required');
                }
                
                // Email validation
                if (field.type === 'email' && field.value.trim()) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(field.value)) {
                        isValid = false;
                        showFieldError(field, 'Please enter a valid email address');
                    }
                }
                
                // Phone validation (basic)
                if (field.type === 'tel' && field.value.trim()) {
                    const phoneRegex = /^[\d\s\-\+\(\)]+$/;
                    if (!phoneRegex.test(field.value)) {
                        isValid = false;
                        showFieldError(field, 'Please enter a valid phone number');
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                const firstError = form.querySelector('.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    validateField(this);
                }
            });
        });
    });
}

/**
 * Show field error message
 */
function showFieldError(field, message) {
    field.classList.add('error');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '0.85rem';
    errorDiv.style.marginTop = '5px';
    errorDiv.textContent = message;
    
    field.parentElement.appendChild(errorDiv);
}

/**
 * Validate single field
 */
function validateField(field) {
    const errorMsg = field.parentElement.querySelector('.error-message');
    if (errorMsg) errorMsg.remove();
    field.classList.remove('error');
    
    if (field.hasAttribute('required') && !field.value.trim()) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    if (field.type === 'email' && field.value.trim()) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(field.value)) {
            showFieldError(field, 'Please enter a valid email address');
            return false;
        }
    }
    
    return true;
}

// ============================================================================
// LOADING SPINNER
// ============================================================================

/**
 * Show loading spinner on form submit
 */
function initLoadingSpinner() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            
            if (submitBtn && !submitBtn.disabled) {
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
                
                // Re-enable after 10 seconds (fallback)
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 10000);
            }
        });
    });
}

// ============================================================================
// ALERT AUTO-DISMISS
// ============================================================================

/**
 * Auto-dismiss success alerts after 5 seconds
 */
function initAlertAutoDismiss() {
    const successAlerts = document.querySelectorAll('.alert-success, .alert.success');
    
    successAlerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
}

// ============================================================================
// BACK TO TOP BUTTON
// ============================================================================

/**
 * Show/hide back to top button
 */
function initBackToTop() {
    // Create back to top button
    const backToTopBtn = document.createElement('button');
    backToTopBtn.innerHTML = '↑';
    backToTopBtn.className = 'back-to-top';
    backToTopBtn.setAttribute('aria-label', 'Back to top');
    backToTopBtn.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background-color: var(--accent-green, #2d5a27);
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 1.5rem;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 999;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    `;
    
    document.body.appendChild(backToTopBtn);
    
    // Show/hide on scroll
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopBtn.style.opacity = '1';
            backToTopBtn.style.visibility = 'visible';
        } else {
            backToTopBtn.style.opacity = '0';
            backToTopBtn.style.visibility = 'hidden';
        }
    });
    
    // Scroll to top on click
    backToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Hover effect
    backToTopBtn.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
        this.style.boxShadow = '0 6px 16px rgba(0,0,0,0.3)';
    });
    
    backToTopBtn.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.2)';
    });
}

// ============================================================================
// TOOLTIPS
// ============================================================================

/**
 * Initialize tooltips for elements with title attribute
 */
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function(e) {
            const tooltipText = this.getAttribute('data-tooltip');
            
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip-custom';
            tooltip.textContent = tooltipText;
            tooltip.style.cssText = `
                position: absolute;
                background-color: #333;
                color: white;
                padding: 8px 12px;
                border-radius: 4px;
                font-size: 0.85rem;
                z-index: 10000;
                white-space: nowrap;
                pointer-events: none;
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + window.pageYOffset + 'px';
            
            this.tooltipElement = tooltip;
        });
        
        element.addEventListener('mouseleave', function() {
            if (this.tooltipElement) {
                this.tooltipElement.remove();
                this.tooltipElement = null;
            }
        });
    });
}

// ============================================================================
// LAZY LOADING IMAGES
// ============================================================================

/**
 * Lazy load images for better performance
 */
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// ============================================================================
// CONFIRMATION DIALOGS
// ============================================================================

/**
 * Add confirmation to delete/dangerous actions
 */
function initConfirmDialogs() {
    const confirmLinks = document.querySelectorAll('[data-confirm]');
    
    confirmLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure?';
            
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

// ============================================================================
// COPY TO CLIPBOARD
// ============================================================================

/**
 * Copy text to clipboard functionality
 */
function initCopyToClipboard() {
    const copyButtons = document.querySelectorAll('[data-copy]');
    
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const textToCopy = this.getAttribute('data-copy');
            
            navigator.clipboard.writeText(textToCopy).then(() => {
                const originalText = this.textContent;
                this.textContent = '✓ Copied!';
                this.style.backgroundColor = '#28a745';
                
                setTimeout(() => {
                    this.textContent = originalText;
                    this.style.backgroundColor = '';
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        });
    });
}

// ============================================================================
// PRINT FUNCTIONALITY
// ============================================================================

/**
 * Print specific sections
 */
function initPrintFunction() {
    const printButtons = document.querySelectorAll('[data-print]');
    
    printButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetSelector = this.getAttribute('data-print');
            const targetElement = document.querySelector(targetSelector);
            
            if (targetElement) {
                const printWindow = window.open('', '', 'width=800,height=600');
                printWindow.document.write('<html><head><title>Print</title>');
                printWindow.document.write('<link rel="stylesheet" href="css/style.css">');
                printWindow.document.write('</head><body>');
                printWindow.document.write(targetElement.innerHTML);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.print();
            }
        });
    });
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Debounce function for performance
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
 * Throttle function for scroll events
 */
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// ============================================================================
// INITIALIZATION
// ============================================================================

/**
 * Initialize all functions when DOM is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Freetown Football Agency - Initializing...');
    
    // Initialize all features
    initMobileMenu();
    initSmoothScroll();
    initStickyHeader();
    initScrollAnimations();
    initFormValidation();
    initLoadingSpinner();
    initAlertAutoDismiss();
    initBackToTop();
    initTooltips();
    initLazyLoading();
    initConfirmDialogs();
    initCopyToClipboard();
    initPrintFunction();
    
    console.log('Freetown Football Agency - Ready!');
});

// ============================================================================
// EXPORT FOR MODULE USAGE (if needed)
// ============================================================================

if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initMobileMenu,
        initSmoothScroll,
        initFormValidation,
        debounce,
        throttle
    };
}