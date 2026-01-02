/**
 * FixIt - Public Problem Reporting System
 * Main JavaScript File
 * Developed by Faraz Hussain & Ali Raza - Sukkur IBA University
 */

// Global variables
let currentPage = window.location.pathname.split('/').pop();

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialize the application
 */
function initializeApp() {
    // Set up event listeners
    setupEventListeners();
    
    // Load reports if on dashboard page
    if (currentPage === 'dashboard.html') {
        loadReports();
    }
}

/**
 * Set up event listeners
 */
function setupEventListeners() {
    // Report form submission
    const reportForm = document.getElementById('reportForm');
    if (reportForm) {
        reportForm.addEventListener('submit', handleReportSubmit);
    }
    
    // Image preview
    const imageInput = document.getElementById('image');
    if (imageInput) {
        imageInput.addEventListener('change', previewImage);
    }
    
    // Auto-detect location button
    const detectLocationBtn = document.getElementById('detectLocation');
    if (detectLocationBtn) {
        detectLocationBtn.addEventListener('click', detectLocation);
    }
}

/**
 * Handle report form submission
 * @param {Event} e - Form submission event
 */
async function handleReportSubmit(e) {
    e.preventDefault();
    
    // Validate form
    if (!validateReportForm()) {
        return;
    }
    
    // Get form data
    const formData = new FormData(e.target);
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.innerHTML = '<span class="loading"></span> Submitting...';
    submitBtn.disabled = true;
    
    try {
        // Submit form data
        const response = await fetch('backend/submit_report.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Report submitted successfully!', 'success');
            e.target.reset(); // Reset form
            
            // Redirect to dashboard after 2 seconds
            setTimeout(() => {
                window.location.href = 'dashboard.html';
            }, 2000);
        } else {
            showAlert(result.message || 'Failed to submit report. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error submitting report:', error);
        showAlert('An error occurred. Please try again.', 'error');
    } finally {
        // Restore button state
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
}

/**
 * Validate report form
 * @returns {boolean} - Validation result
 */
function validateReportForm() {
    const description = document.getElementById('description').value.trim();
    const location = document.getElementById('location').value.trim();
    const image = document.getElementById('image').files[0];
    
    let isValid = true;
    let errors = [];
    
    // Clear previous errors
    clearFormErrors();
    
    // Validate description
    if (!description) {
        errors.push('Description is required');
        showFieldError('description', 'Description is required');
        isValid = false;
    } else if (description.length < 10) {
        errors.push('Description must be at least 10 characters');
        showFieldError('description', 'Description must be at least 10 characters');
        isValid = false;
    }
    
    // Validate location
    if (!location) {
        errors.push('Location is required');
        showFieldError('location', 'Location is required');
        isValid = false;
    }
    
    // Validate image (optional but check if provided)
    if (image) {
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!validTypes.includes(image.type)) {
            errors.push('Please upload a valid image file (JPEG, PNG, or GIF)');
            showFieldError('image', 'Please upload a valid image file (JPEG, PNG, or GIF)');
            isValid = false;
        } else if (image.size > maxSize) {
            errors.push('Image size must be less than 5MB');
            showFieldError('image', 'Image size must be less than 5MB');
            isValid = false;
        }
    }
    
    // Show general errors if any
    if (errors.length > 0) {
        showAlert('Please fix the following errors: ' + errors.join(', '), 'error');
    }
    
    return isValid;
}

/**
 * Show field-specific error
 * @param {string} fieldId - Field ID
 * @param {string} message - Error message
 */
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorDiv = document.createElement('div');
    errorDiv.className = 'text-danger';
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
    field.classList.add('is-invalid');
}

/**
 * Clear all form errors
 */
function clearFormErrors() {
    const errorElements = document.querySelectorAll('.text-danger');
    errorElements.forEach(el => el.remove());
    
    const invalidFields = document.querySelectorAll('.is-invalid');
    invalidFields.forEach(field => field.classList.remove('is-invalid'));
}

/**
 * Preview uploaded image
 * @param {Event} e - File input change event
 */
function previewImage(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    
    if (file && preview) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; margin-top: 1rem;">`;
        };
        
        reader.readAsDataURL(file);
    }
}

/**
 * Detect user location using Geolocation API
 */
function detectLocation() {
    const locationInput = document.getElementById('location');
    const detectBtn = document.getElementById('detectLocation');
    
    if (!navigator.geolocation) {
        showAlert('Geolocation is not supported by your browser.', 'error');
        return;
    }
    
    // Show loading state
    const originalText = detectBtn.textContent;
    detectBtn.innerHTML = '<span class="loading"></span> Detecting...';
    detectBtn.disabled = true;
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            // Use reverse geocoding to get address
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            // For demo purposes, show coordinates as location
            // In production, you would use Google Maps API or similar service
            locationInput.value = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
            
            // Restore button state
            detectBtn.textContent = originalText;
            detectBtn.disabled = false;
            
            showAlert('Location detected successfully!', 'success');
        },
        function(error) {
            let message = 'Unable to detect location. ';
            
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    message += 'Please allow location access.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    message += 'Location information is unavailable.';
                    break;
                case error.TIMEOUT:
                    message += 'Location request timed out.';
                    break;
                default:
                    message += 'An unknown error occurred.';
                    break;
            }
            
            showAlert(message, 'error');
            
            // Restore button state
            detectBtn.textContent = originalText;
            detectBtn.disabled = false;
        }
    );
}

/**
 * Load reports for dashboard
 */
async function loadReports() {
    const reportsContainer = document.getElementById('reportsContainer');
    
    if (!reportsContainer) return;
    
    try {
        // Show loading state
        reportsContainer.innerHTML = '<div class="text-center"><div class="loading"></div><p>Loading reports...</p></div>';
        
        const response = await fetch('backend/get_reports.php');
        const reports = await response.json();
        
        if (reports.success) {
            displayReports(reports.data);
        } else {
            reportsContainer.innerHTML = '<div class="alert alert-error">Failed to load reports.</div>';
        }
    } catch (error) {
        console.error('Error loading reports:', error);
        reportsContainer.innerHTML = '<div class="alert alert-error">Error loading reports. Please refresh the page.</div>';
    }
}

/**
 * Display reports in the dashboard
 * @param {Array} reports - Array of report objects
 */
function displayReports(reports) {
    const reportsContainer = document.getElementById('reportsContainer');
    
    if (!reports || reports.length === 0) {
        reportsContainer.innerHTML = '<div class="text-center"><p>No reports found.</p></div>';
        return;
    }
    
    let html = '<div class="reports-grid">';
    
    reports.forEach(report => {
        const statusClass = `status-${report.status.toLowerCase().replace(' ', '-')}`;
        const imagePath = report.image ? `assets/images/${report.image}` : 'assets/images/placeholder.jpg';
        
        html += `
            <div class="report-card">
                <img src="${imagePath}" alt="Report Image" class="report-image" onerror="this.src='assets/images/placeholder.jpg'">
                <div class="report-content">
                    <div class="report-meta">
                        <span><i class="fas fa-calendar"></i> ${formatDate(report.created_at)}</span>
                        <span><i class="fas fa-map-marker-alt"></i> ${report.location}</span>
                    </div>
                    <p class="report-description">${report.description}</p>
                    <div class="report-status ${statusClass}">${report.status}</div>
                    <div class="mt-2">
                        <select class="status-select" onchange="updateReportStatus(${report.id}, this.value)">
                            <option value="Pending" ${report.status === 'Pending' ? 'selected' : ''}>Pending</option>
                            <option value="In Progress" ${report.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                            <option value="Resolved" ${report.status === 'Resolved' ? 'selected' : ''}>Resolved</option>
                        </select>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    reportsContainer.innerHTML = html;
}

/**
 * Update report status
 * @param {number} reportId - Report ID
 * @param {string} newStatus - New status
 */
async function updateReportStatus(reportId, newStatus) {
    try {
        const response = await fetch('backend/update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: reportId,
                status: newStatus
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Status updated successfully!', 'success');
            // Reload reports to reflect changes
            setTimeout(() => loadReports(), 1000);
        } else {
            showAlert('Failed to update status. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error updating status:', error);
        showAlert('Error updating status. Please try again.', 'error');
    }
}

/**
 * Show alert message
 * @param {string} message - Alert message
 * @param {string} type - Alert type (success, error, info)
 */
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    // Insert at top of main content or body
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 5000);
    
    // Scroll to alert
    alertDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/**
 * Format date for display
 * @param {string} dateString - Date string
 * @returns {string} - Formatted date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Utility function to check if user is on mobile
 * @returns {boolean} - True if mobile device
 */
function isMobile() {
    return window.innerWidth <= 768;
}

/**
 * Smooth scroll to element
 * @param {string} elementId - Element ID
 */
function scrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
}