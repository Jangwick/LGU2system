/**
 * Authentication JavaScript
 * Handles login, registration, password reset functionality
 */

// Set Loading State Helper
function setLoadingState(button, isLoading) {
    if (!button) return;
    
    if (isLoading) {
        button.disabled = true;
        button.classList.add('opacity-75', 'cursor-not-allowed');
    } else {
        button.disabled = false;
        button.classList.remove('opacity-75', 'cursor-not-allowed');
    }
}

// Login Form Handler
const loginForm = document.getElementById('login-form');
if (loginForm) {
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const loginBtn = document.getElementById('login-btn');
        const loginBtnText = document.getElementById('login-btn-text');
        const alertContainer = document.getElementById('alert-container');
        
        // Get form data
        const formData = new FormData(this);
        const email = formData.get('email');
        const password = formData.get('password');
        const remember = formData.get('remember');
        
        // Basic validation
        if (!email || !password) {
            showAlert('Please fill in all fields', 'error');
            return;
        }
        
        // Show loading state
        setLoadingState(loginBtn, true);
        loginBtnText.innerHTML = '<i class="bi bi-hourglass-split mr-2"></i>Signing in...';
        
        try {
            // Make API call
            const response = await fetch(App.config.endpoints.login, {
                method: 'POST',
                body: formData
            });
            
            const text = await response.text();
            let result;
            
            try {
                result = JSON.parse(text);
            } catch (e) {
                console.error('Invalid JSON response:', text);
                showAlert('Server error. Please check the console for details.', 'error');
                setLoadingState(loginBtn, false);
                loginBtnText.textContent = 'Sign In';
                return;
            }
            
            if (result.success) {
                showAlert('Login successful! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = result.redirect || App.config.urls.dashboard + '/views/index.php';
                }, 1000);
            } else {
                showAlert(result.message || 'Invalid credentials', 'error');
                setLoadingState(loginBtn, false);
                loginBtnText.textContent = 'Sign In';
            }
        } catch (error) {
            console.error('Login error:', error);
            showAlert('An error occurred. Please try again.', 'error');
            setLoadingState(loginBtn, false);
            loginBtnText.textContent = 'Sign In';
        }
    });
}

// Registration Form Handler
const registerForm = document.getElementById('register-form');
if (registerForm) {
    registerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const registerBtn = document.getElementById('register-btn');
        const registerBtnText = document.getElementById('register-btn-text');
        
        // Get form data
        const formData = new FormData(this);
        const password = formData.get('password');
        const confirmPassword = formData.get('confirm_password');
        const terms = formData.get('terms');
        
        // Validation
        if (password !== confirmPassword) {
            showAlert('Passwords do not match', 'error');
            return;
        }
        
        if (password.length < 8) {
            showAlert('Password must be at least 8 characters long', 'error');
            return;
        }
        
        if (!terms) {
            showAlert('Please agree to the terms and conditions', 'error');
            return;
        }
        
        // Show loading state
        setLoadingState(registerBtn, true);
        registerBtnText.innerHTML = '<i class="bi bi-hourglass-split mr-2"></i>Creating account...';
        
        try {
            const response = await fetch(App.config.endpoints.register, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showAlert('Account created successfully! Redirecting to login...', 'success');
                setTimeout(() => {
                    window.location.href = App.config.urls.auth + '/views/login.php';
                }, 2000);
            } else {
                showAlert(result.message || 'Registration failed', 'error');
                setLoadingState(registerBtn, false);
                registerBtnText.textContent = 'Create Account';
            }
        } catch (error) {
            console.error('Registration error:', error);
            showAlert('An error occurred. Please try again.', 'error');
            setLoadingState(registerBtn, false);
            registerBtnText.textContent = 'Create Account';
        }
    });
}

// Forgot Password Form Handler
const forgotPasswordForm = document.getElementById('forgot-password-form');
if (forgotPasswordForm) {
    forgotPasswordForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submit-btn');
        const submitBtnText = document.getElementById('submit-btn-text');
        
        const formData = new FormData(this);
        
        setLoadingState(submitBtn, true);
        submitBtnText.innerHTML = '<i class="bi bi-hourglass-split mr-2"></i>Sending...';
        
        try {
            const response = await fetch(App.url('modules/authentication/controllers/ForgotPasswordController.php'), {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showAlert('Reset link sent! Check your email.', 'success');
            } else {
                showAlert(result.message || 'Failed to send reset link', 'error');
            }
            
            setLoadingState(submitBtn, false);
            submitBtnText.textContent = 'Send Reset Link';
        } catch (error) {
            console.error('Password reset error:', error);
            showAlert('An error occurred. Please try again.', 'error');
            setLoadingState(submitBtn, false);
            submitBtnText.textContent = 'Send Reset Link';
        }
    });
}

// Show Alert Helper
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;
    
    const alertColors = {
        success: 'bg-green-50 border-green-200 text-green-800',
        error: 'bg-red-50 border-red-200 text-red-800',
        warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
        info: 'bg-blue-50 border-blue-200 text-blue-800'
    };
    
    const alertIcons = {
        success: 'check-circle-fill',
        error: 'x-circle-fill',
        warning: 'exclamation-triangle-fill',
        info: 'info-circle-fill'
    };
    
    const alert = document.createElement('div');
    alert.className = `${alertColors[type]} border px-4 py-3 rounded-lg flex items-center animate-fade-in`;
    alert.innerHTML = `
        <i class="bi bi-${alertIcons[type]} mr-3"></i>
        <span>${message}</span>
    `;
    
    alertContainer.innerHTML = '';
    alertContainer.appendChild(alert);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        alert.classList.add('animate-fade-out');
        setTimeout(() => alert.remove(), 300);
    }, 5000);
}

// Email validation
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Real-time email validation
document.querySelectorAll('input[type="email"]').forEach(emailInput => {
    emailInput.addEventListener('blur', function() {
        if (this.value && !validateEmail(this.value)) {
            this.classList.add('border-red-500');
            const errorId = this.id + '-error';
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.textContent = 'Please enter a valid email address';
                errorElement.classList.remove('hidden');
            }
        } else {
            this.classList.remove('border-red-500');
            const errorId = this.id + '-error';
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.classList.add('hidden');
            }
        }
    });
});

console.log('Auth JS Loaded');
