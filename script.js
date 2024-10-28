// Form validation helper
function validateForm(username, mobile) {
    const errors = [];
    if (!username || username.length < 3) {
        errors.push('Username must be at least 3 characters');
    }
    if (!mobile || !/^\d{10}$/.test(mobile)) {
        errors.push('Please enter a valid 10-digit mobile number');
    }
    return errors;
}

// UI feedback helper
function showFeedback(message, isError = false) {
    const feedbackDiv = document.getElementById('feedback-message') || 
        (() => {
            const div = document.createElement('div');
            div.id = 'feedback-message';
            document.body.appendChild(div);
            return div;
        })();
    
    feedbackDiv.textContent = message;
    feedbackDiv.className = `alert ${isError ? 'alert-danger' : 'alert-success'}`;
    feedbackDiv.style.display = 'block';
    
    setTimeout(() => {
        feedbackDiv.style.display = 'none';
    }, 5000);
}

function showRegistration() {
    const form = document.getElementById('registration-form');
    if (form) {
        form.style.display = 'block';
    } else {
        console.error('Registration form element not found');
    }
}

function showInterface(type) {
    showFeedback(`${type} interface is under construction. Please check back later.`);
}

// Event listeners
document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            
            const username = document.getElementById('username')?.value.trim();
            const mobile = document.getElementById('mobile')?.value.trim();
            
            // Validate form
            const errors = validateForm(username, mobile);
            if (errors.length > 0) {
                showFeedback(errors.join('\n'), true);
                return;
            }
            
            try {
                const response = await fetch('/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ username, mobile })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                showFeedback('Registration successful! Welcome aboard.');
                registerForm.reset();
                
            } catch (error) {
                console.error('Registration error:', error);
                showFeedback('Registration failed. Please try again later.', true);
            }
        });
    }
    
    // Replace individual function calls with generic interface function
    document.getElementById('driver-interface')?.addEventListener('click', () => showInterface('Driver'));
    document.getElementById('admin-interface')?.addEventListener('click', () => showInterface('Admin'));
});