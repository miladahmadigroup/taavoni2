// File Upload with Progress Bar - Enhanced Version
function initUploadProgress() {
    const forms = document.querySelectorAll('form[enctype="multipart/form-data"]');
    
    forms.forEach(form => {
        // Skip forms that already have custom handling
        if (form.id === 'paymentForm') return;
        
        form.addEventListener('submit', function(e) {
            const fileInputs = form.querySelectorAll('input[type="file"]');
            let hasFiles = false;
            
            fileInputs.forEach(input => {
                if (input.files && input.files.length > 0) {
                    hasFiles = true;
                }
            });
            
            if (hasFiles) {
                e.preventDefault();
                uploadWithProgress(form);
            }
        });
    });
}

function uploadWithProgress(form) {
    const formData = new FormData(form);
    const progressContainer = createProgressBar();
    
    // Insert progress bar before form
    form.parentNode.insertBefore(progressContainer, form);
    
    // Disable form submit button
    const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        const originalText = submitBtn.innerHTML;
        submitBtn.setAttribute('data-original-text', originalText);
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال آپلود...';
    }
    
    const xhr = new XMLHttpRequest();
    
    // Upload progress
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            updateProgressBar(progressContainer, percentComplete);
        }
    });
    
    // Upload complete
    xhr.addEventListener('load', function() {
        if (xhr.status === 200) {
            updateProgressBar(progressContainer, 100, 'آپلود کامل شد!');
            
            setTimeout(() => {
                // Remove progress bar
                progressContainer.remove();
                
                // Check response for success message
                const response = xhr.responseText;
                if (response.includes('موفقیت') || response.includes('ثبت شد')) {
                    // Show success message
                    showSuccessMessage('پرداخت با موفقیت ثبت شد!');
                    
                    // Reset form
                    form.reset();
                    
                    // Close modal if exists
                    const modal = form.closest('.modal');
                    if (modal) {
                        const modalInstance = bootstrap.Modal.getInstance(modal);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                    }
                    
                    // Reload page after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else if (response.includes('window.location')) {
                    eval(response);
                } else {
                    // Enable form and show response
                    enableForm(form, submitBtn);
                    if (response.includes('خطا')) {
                        showErrorMessage('خطا در ثبت پرداخت');
                    } else {
                        // If no clear success or error, assume success
                        showSuccessMessage('عملیات با موفقیت انجام شد!');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                }
            }, 500);
        } else {
            showError(progressContainer, 'خطا در آپلود فایل!');
            enableForm(form, submitBtn);
        }
    });
    
    // Upload error
    xhr.addEventListener('error', function() {
        showError(progressContainer, 'خطا در اتصال!');
        enableForm(form, submitBtn);
    });
    
    // Send request
    xhr.open('POST', form.action || window.location.href);
    xhr.send(formData);
}

function createProgressBar() {
    const container = document.createElement('div');
    container.className = 'upload-progress-container mb-3';
    container.innerHTML = `
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="upload-status">در حال آپلود...</span>
                    <span class="upload-percentage">0%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 0%"></div>
                </div>
            </div>
        </div>
    `;
    return container;
}

function updateProgressBar(container, percentage, statusText = null) {
    const progressBar = container.querySelector('.progress-bar');
    const percentageSpan = container.querySelector('.upload-percentage');
    const statusSpan = container.querySelector('.upload-status');
    
    progressBar.style.width = percentage + '%';
    percentageSpan.textContent = Math.round(percentage) + '%';
    
    if (statusText) {
        statusSpan.textContent = statusText;
    }
    
    if (percentage >= 100) {
        progressBar.classList.remove('progress-bar-animated');
        progressBar.classList.add('bg-success');
        statusSpan.textContent = 'آپلود کامل شد!';
    }
}

function showError(container, errorMessage) {
    const statusSpan = container.querySelector('.upload-status');
    const progressBar = container.querySelector('.progress-bar');
    
    statusSpan.textContent = errorMessage;
    progressBar.classList.remove('progress-bar-animated');
    progressBar.classList.add('bg-danger');
    progressBar.style.width = '100%';
}

function enableForm(form, submitBtn) {
    if (submitBtn) {
        submitBtn.disabled = false;
        const originalText = submitBtn.getAttribute('data-original-text');
        if (originalText) {
            submitBtn.innerHTML = originalText;
        } else {
            submitBtn.innerHTML = 'ثبت پرداخت';
        }
    }
    
    // Remove any existing progress containers
    const progressContainers = form.parentNode.querySelectorAll('.upload-progress-container');
    progressContainers.forEach(container => container.remove());
}

function showSuccessMessage(message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.temp-alert');
    existingAlerts.forEach(alert => alert.remove());
    
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show temp-alert';
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.innerHTML = `
        <i class="fas fa-check-circle"></i> ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}

function showErrorMessage(message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.temp-alert');
    existingAlerts.forEach(alert => alert.remove());
    
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show temp-alert';
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i> ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// File preview functionality
function initFilePreview() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                showFilePreview(this, file);
            }
        });
    });
}

function showFilePreview(input, file) {
    // Remove existing preview
    const existingPreview = input.parentNode.querySelector('.file-preview');
    if (existingPreview) {
        existingPreview.remove();
    }
    
    const preview = document.createElement('div');
    preview.className = 'file-preview mt-2 p-2 border rounded';
    
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div class="d-flex align-items-center">
                    <img src="${e.target.result}" alt="پیش‌نمایش" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                    <div class="ms-3">
                        <div class="fw-bold">${file.name}</div>
                        <small class="text-muted">${formatFileSize(file.size)}</small>
                    </div>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-file fa-3x text-muted"></i>
                <div class="ms-3">
                    <div class="fw-bold">${file.name}</div>
                    <small class="text-muted">${formatFileSize(file.size)}</small>
                </div>
            </div>
        `;
    }
    
    input.parentNode.appendChild(preview);
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 بایت';
    const k = 1024;
    const sizes = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initUploadProgress();
    initFilePreview();
    
    // Store original button text
    const submitBtns = document.querySelectorAll('button[type="submit"], input[type="submit"]');
    submitBtns.forEach(btn => {
        btn.setAttribute('data-original-text', btn.innerHTML);
    });
});