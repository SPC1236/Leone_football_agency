// js/form-validation.js - Advanced Form Validation
// Comprehensive form validation for all forms

'use strict';

// ============================================================================
// VALIDATION RULES
// ============================================================================

const validationRules = {
    required: {
        test: (value) => value.trim() !== '',
        message: 'This field is required'
    },
    email: {
        test: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
        message: 'Please enter a valid email address'
    },
    phone: {
        test: (value) => /^[\d\s\-\+\(\)]{8,}$/.test(value),
        message: 'Please enter a valid phone number'
    },
    minLength: {
        test: (value, param) => value.length >= param,
        message: (param) => `Must be at least ${param} characters`
    },
    maxLength: {
        test: (value, param) => value.length <= param,
        message: (param) => `Must not exceed ${param} characters`
    },
    min: {
        test: (value, param) => parseFloat(value) >= param,
        message: (param) => `Must be at least ${param}`
    },
    max: {
        test: (value, param) => parseFloat(value) <= param,
        message: (param) => `Must not exceed ${param}`
    },
    pattern: {
        test: (value, param) => new RegExp(param).test(value),
        message: 'Invalid format'
    },
    number: {
        test: (value) => !isNaN(parseFloat(value)) && isFinite(value),
        message: 'Please enter a valid number'
    },
    integer: {
        test: (value) => /^\d+$/.test(value),
        message: 'Please enter a whole number'
    },
    url: {
        test: (value) => /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/.test(value),
        message: 'Please enter a valid URL'
    },
    password: {
        test: (value) => value.length >= 8 && /[A-Z]/.test(value) && /[a-z]/.test(value) && /[0-9]/.test(value),
        message: 'Password must be 8+ characters with uppercase, lowercase, and number'
    },
    username: {
        test: (value) => /^[a-zA-Z0-9_]{4,}$/.test(value),
        message: 'Username must be 4+ characters (letters, numbers, underscore only)'
    },
    match: {
        test: (value, param) => value === document.querySelector(param)?.value,
        message: 'Fields do not match'
    }
};

// ============================================================================
// FORM VALIDATOR CLASS
// ============================================================================

class FormValidator {
    constructor(form, options = {}) {
        this.form = form;
        this.options = {
            realTime: true,
            submitButton: form.querySelector('button[type="submit"]'),
            errorClass: 'error',
            successClass: 'success',
            ...options
        };
        
        this.fields = [];
        this.init();
    }
    
    init() {
        // Collect all fields with validation
        const inputs = this.form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            if (input.hasAttribute('data-validate') || input.required) {
                this.fields.push({
                    element: input,
                    rules: this.parseRules(input)
                });
                
                // Real-time validation
                if (this.options.realTime) {
                    input.addEventListener('blur', () => this.validateField(input));
                    input.addEventListener('input', () => {
                        if (input.classList.contains(this.options.errorClass)) {
                            this.validateField(input);
                        }
                    });
                }
            }
        });
        
        // Form submit validation
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }
    
    parseRules(input) {
        const rules = [];
        
        // Required
        if (input.required) {
            rules.push({ rule: 'required' });
        }
        
        // Data-validate attribute
        const validateAttr = input.getAttribute('data-validate');
        if (validateAttr) {
            validateAttr.split('|').forEach(rule => {
                const [ruleName, param] = rule.split(':');
                rules.push({ rule: ruleName, param });
            });
        }
        
        // HTML5 attributes
        if (input.type === 'email') rules.push({ rule: 'email' });
        if (input.type === 'tel') rules.push({ rule: 'phone' });
        if (input.type === 'number') rules.push({ rule: 'number' });
        if (input.type === 'url') rules.push({ rule: 'url' });
        
        if (input.minLength && input.minLength > 0) {
            rules.push({ rule: 'minLength', param: input.minLength });
        }
        if (input.maxLength && input.maxLength > 0) {
            rules.push({ rule: 'maxLength', param: input.maxLength });
        }
        if (input.min) {
            rules.push({ rule: 'min', param: parseFloat(input.min) });
        }
        if (input.max) {
            rules.push({ rule: 'max', param: parseFloat(input.max) });
        }
        if (input.pattern) {
            rules.push({ rule: 'pattern', param: input.pattern });
        }
        
        return rules;
    }
    
    validateField(input) {
        const field = this.fields.find(f => f.element === input);
        if (!field) return true;
        
        this.clearFieldError(input);
        
        const value = input.value;
        
        for (let { rule, param } of field.rules) {
            const validation = validationRules[rule];
            
            if (validation && !validation.test(value, param)) {
                const message = typeof validation.message === 'function' 
                    ? validation.message(param) 
                    : validation.message;
                this.showFieldError(input, message);
                return false;
            }
        }
        
        this.showFieldSuccess(input);
        return true;
    }
    
    showFieldError(input, message) {
        input.classList.add(this.options.errorClass);
        input.classList.remove(this.options.successClass);
        
        const errorElement = document.createElement('div');
        errorElement.className = 'validation-error';
        errorElement.style.cssText = `
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 5px;
            animation: shake 0.3s ease;
        `;
        errorElement.textContent = message;
        
        const parent = input.parentElement;
        parent.appendChild(errorElement);
        
        // Add shake animation
        input.style.animation = 'shake 0.3s ease';
        setTimeout(() => input.style.animation = '', 300);
    }
    
    showFieldSuccess(input) {
        input.classList.remove(this.options.errorClass);
        input.classList.add(this.options.successClass);
    }
    
    clearFieldError(input) {
        input.classList.remove(this.options.errorClass, this.options.successClass);
        
        const parent = input.parentElement;
        const errorElement = parent.querySelector('.validation-error');
        if (errorElement) {
            errorElement.remove();
        }
    }
    
    handleSubmit(e) {
        let isValid = true;
        
        this.fields.forEach(field => {
            if (!this.validateField(field.element)) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            
            // Scroll to first error
            const firstError = this.form.querySelector(`.${this.options.errorClass}`);
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            
            // Show notification
            if (window.showNotification) {
                window.showNotification('Please fix the errors in the form', 'error');
            }
        } else {
            // Disable submit button to prevent double submission
            if (this.options.submitButton) {
                this.options.submitButton.disabled = true;
                const originalText = this.options.submitButton.innerHTML;
                this.options.submitButton.innerHTML = '<span class="spinner"></span> Submitting...';
                
                // Re-enable after 10 seconds (fallback)
                setTimeout(() => {
                    this.options.submitButton.disabled = false;
                    this.options.submitButton.innerHTML = originalText;
                }, 10000);
            }
        }
        
        return isValid;
    }
    
    reset() {
        this.form.reset();
        this.fields.forEach(field => {
            this.clearFieldError(field.element);
        });
    }
}

// ============================================================================
// PASSWORD STRENGTH METER
// ============================================================================

class PasswordStrengthMeter {
    constructor(passwordInput, options = {}) {
        this.passwordInput = passwordInput;
        this.options = {
            container: passwordInput.parentElement,
            showStrength: true,
            showRequirements: true,
            ...options
        };
        
        this.init();
    }
    
    init() {
        // Create strength meter
        if (this.options.showStrength) {
            const meterHTML = `
                <div class="password-strength-meter" style="margin-top: 8px;">
                    <div class="strength-bar" style="height: 4px; background: #e0e0e0; border-radius: 2px; overflow: hidden;">
                        <div class="strength-bar-fill" style="height: 100%; width: 0%; transition: all 0.3s ease;"></div>
                    </div>
                    <div class="strength-text" style="font-size: 0.85rem; margin-top: 4px; color: #666;"></div>
                </div>
            `;
            this.options.container.insertAdjacentHTML('beforeend', meterHTML);
            this.strengthBar = this.options.container.querySelector('.strength-bar-fill');
            this.strengthText = this.options.container.querySelector('.strength-text');
        }
        
        // Create requirements list
        if (this.options.showRequirements) {
            const requirementsHTML = `
                <div class="password-requirements" style="margin-top: 8px; font-size: 0.85rem;">
                    <div data-requirement="length" style="color: #666;">✓ At least 8 characters</div>
                    <div data-requirement="uppercase" style="color: #666;">✓ One uppercase letter</div>
                    <div data-requirement="lowercase" style="color: #666;">✓ One lowercase letter</div>
                    <div data-requirement="number" style="color: #666;">✓ One number</div>
                </div>
            `;
            this.options.container.insertAdjacentHTML('beforeend', requirementsHTML);
        }
        
        // Listen to input
        this.passwordInput.addEventListener('input', () => this.updateStrength());
    }
    
    updateStrength() {
        const password = this.passwordInput.value;
        const strength = this.calculateStrength(password);
        
        // Update meter
        if (this.strengthBar && this.strengthText) {
            this.strengthBar.style.width = strength.percentage + '%';
            this.strengthBar.style.backgroundColor = strength.color;
            this.strengthText.textContent = strength.label;
            this.strengthText.style.color = strength.color;
        }
        
        // Update requirements
        const requirements = this.options.container.querySelectorAll('[data-requirement]');
        requirements.forEach(req => {
            const type = req.getAttribute('data-requirement');
            const met = this.checkRequirement(password, type);
            req.style.color = met ? '#28a745' : '#666';
            req.style.fontWeight = met ? '600' : '400';
        });
    }
    
    calculateStrength(password) {
        let score = 0;
        
        if (password.length >= 8) score += 25;
        if (password.length >= 12) score += 15;
        if (/[a-z]/.test(password)) score += 20;
        if (/[A-Z]/.test(password)) score += 20;
        if (/[0-9]/.test(password)) score += 20;
        if (/[^a-zA-Z0-9]/.test(password)) score += 20;
        
        if (score <= 40) {
            return { percentage: score, color: '#dc3545', label: 'Weak' };
        } else if (score <= 70) {
            return { percentage: score, color: '#ffc107', label: 'Medium' };
        } else {
            return { percentage: score, color: '#28a745', label: 'Strong' };
        }
    }
    
    checkRequirement(password, type) {
        switch(type) {
            case 'length': return password.length >= 8;
            case 'uppercase': return /[A-Z]/.test(password);
            case 'lowercase': return /[a-z]/.test(password);
            case 'number': return /[0-9]/.test(password);
            default: return false;
        }
    }
}

// ============================================================================
// PASSWORD VISIBILITY TOGGLE
// ============================================================================

function initPasswordToggle() {
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    
    passwordInputs.forEach(input => {
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.innerHTML = '👁️';
        toggleBtn.style.cssText = `
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            opacity: 0.6;
            transition: opacity 0.3s;
        `;
        
        toggleBtn.addEventListener('mouseenter', () => toggleBtn.style.opacity = '1');
        toggleBtn.addEventListener('mouseleave', () => toggleBtn.style.opacity = '0.6');
        
        toggleBtn.addEventListener('click', function() {
            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML = '🙈';
            } else {
                input.type = 'password';
                this.innerHTML = '👁️';
            }
        });
        
        wrapper.appendChild(toggleBtn);
        input.style.paddingRight = '45px';
    });
}

// ============================================================================
// INITIALIZATION
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Form Validation - Initializing...');
    
    // Initialize all forms with data-validate attribute
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => new FormValidator(form));
    
    // Initialize password strength meters
    const passwordFields = document.querySelectorAll('input[type="password"][data-strength]');
    passwordFields.forEach(field => new PasswordStrengthMeter(field));
    
    // Initialize password toggles
    initPasswordToggle();
    
    console.log('Form Validation - Ready!');
});

// Add shake animation CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    .spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { FormValidator, PasswordStrengthMeter };
}