// Student Course Management System JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initFormValidation();
    initSearchFunctionality();
    initDeleteConfirmations();
});

// Form Validation
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            showFieldError(input, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(input);
        }
    });
    
    return isValid;
}

function showFieldError(field, message) {
    clearFieldError(field);
    field.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('error');
    const errorDiv = field.parentNode.querySelector('.error-message');
    if (errorDiv) errorDiv.remove();
}

// Search Functionality
function initSearchFunctionality() {
    const searchInputs = document.querySelectorAll('.search-box input');
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const table = this.closest('.container').querySelector('.table');
            if (table) filterTable(table, searchTerm);
        });
    });
}

function filterTable(table, searchTerm) {
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

// Delete Confirmations
function initDeleteConfirmations() {
    document.addEventListener('click', function(e) {
        if (e.target.matches('.btn-delete')) {
            e.preventDefault();
            const message = e.target.getAttribute('data-confirm') || 'Are you sure?';
            if (confirm(message)) {
                const form = e.target.closest('form');
                if (form) form.submit();
                else {
                    const href = e.target.getAttribute('href');
                    if (href) window.location.href = href;
                }
            }
        }
    });
}

// Utility Functions
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `${message}<button type="button" class="alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>`;
    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);
    setTimeout(() => alertDiv.remove(), 5000);
}
