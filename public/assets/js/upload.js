/**
 * File Upload JavaScript
 * Handles drag-and-drop, file validation, and upload progress
 */

class FileUploader {
    constructor(options = {}) {
        this.dropZone = options.dropZone || document.getElementById('drop-zone');
        this.fileInput = options.fileInput || document.getElementById('file-input');
        this.maxFileSize = options.maxFileSize || 50 * 1024 * 1024; // 50MB default
        this.allowedTypes = options.allowedTypes || [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ];
        
        this.init();
    }
    
    init() {
        if (!this.dropZone || !this.fileInput) return;
        
        this.attachEventListeners();
    }
    
    attachEventListeners() {
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            this.dropZone.addEventListener(eventName, this.preventDefaults, false);
            document.body.addEventListener(eventName, this.preventDefaults, false);
        });
        
        // Highlight drop zone
        ['dragenter', 'dragover'].forEach(eventName => {
            this.dropZone.addEventListener(eventName, () => this.highlight(), false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            this.dropZone.addEventListener(eventName, () => this.unhighlight(), false);
        });
        
        // Handle dropped files
        this.dropZone.addEventListener('drop', (e) => this.handleDrop(e), false);
        
        // Handle file input change
        this.fileInput.addEventListener('change', (e) => this.handleFileSelect(e), false);
    }
    
    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    highlight() {
        this.dropZone.classList.add('border-blue-500', 'bg-blue-50');
    }
    
    unhighlight() {
        this.dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    }
    
    handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        this.handleFiles(files);
    }
    
    handleFileSelect(e) {
        const files = e.target.files;
        this.handleFiles(files);
    }
    
    handleFiles(files) {
        if (files.length === 0) return;
        
        // Take first file only
        const file = files[0];
        
        // Validate file
        if (!this.validateFile(file)) {
            return;
        }
        
        // Display file preview
        this.displayFilePreview(file);
    }
    
    validateFile(file) {
        // Check file size
        if (file.size > this.maxFileSize) {
            toast.show(`File is too large. Maximum size is ${formatFileSize(this.maxFileSize)}`, 'error');
            return false;
        }
        
        // Check file type
        if (!this.allowedTypes.includes(file.type)) {
            toast.show('Invalid file type. Please upload PDF, DOC, DOCX, XLS, XLSX, PPT, or PPTX', 'error');
            return false;
        }
        
        return true;
    }
    
    displayFilePreview(file) {
        const preview = document.getElementById('file-preview');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        
        if (preview && fileName && fileSize) {
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            preview.classList.remove('hidden');
            this.dropZone.classList.add('hidden');
        }
        
        // Get file icon
        const iconElement = preview?.querySelector('.file-icon');
        if (iconElement) {
            iconElement.className = `bi bi-${this.getFileIcon(file.name)} text-2xl`;
        }
    }
    
    getFileIcon(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        const iconMap = {
            'pdf': 'file-pdf text-red-600',
            'doc': 'file-word text-blue-600',
            'docx': 'file-word text-blue-600',
            'xls': 'file-excel text-green-600',
            'xlsx': 'file-excel text-green-600',
            'ppt': 'file-ppt text-orange-600',
            'pptx': 'file-ppt text-orange-600'
        };
        
        return iconMap[ext] || 'file-earmark text-gray-600';
    }
    
    async uploadFile(formData, onProgress) {
        try {
            const xhr = new XMLHttpRequest();
            
            return new Promise((resolve, reject) => {
                // Progress tracking
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        if (onProgress) {
                            onProgress(percentComplete);
                        }
                    }
                });
                
                // Load event
                xhr.addEventListener('load', () => {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            resolve(response);
                        } catch (error) {
                            reject(new Error('Invalid response from server'));
                        }
                    } else {
                        reject(new Error(`Upload failed with status ${xhr.status}`));
                    }
                });
                
                // Error event
                xhr.addEventListener('error', () => {
                    reject(new Error('Upload failed'));
                });
                
                // Abort event
                xhr.addEventListener('abort', () => {
                    reject(new Error('Upload cancelled'));
                });
                
                // Send request
                xhr.open('POST', '/modules/document-management/controllers/DocumentController.php');
                xhr.send(formData);
            });
        } catch (error) {
            console.error('Upload error:', error);
            throw error;
        }
    }
}

// Initialize file uploader
const fileUploader = new FileUploader();

// Remove file button
document.getElementById('remove-file')?.addEventListener('click', function() {
    const fileInput = document.getElementById('file-input');
    const filePreview = document.getElementById('file-preview');
    const dropZone = document.getElementById('drop-zone');
    
    if (fileInput) fileInput.value = '';
    if (filePreview) filePreview.classList.add('hidden');
    if (dropZone) dropZone.classList.remove('hidden');
});

// Upload form submission with progress
const uploadForm = document.getElementById('upload-form');
if (uploadForm) {
    uploadForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = uploadForm.querySelector('button[type="submit"]');
        const formData = new FormData(this);
        
        // Create progress bar
        const progressBar = createProgressBar();
        uploadForm.appendChild(progressBar);
        
        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split mr-2 animate-spin"></i>Uploading...';
        
        try {
            const response = await fileUploader.uploadFile(formData, (progress) => {
                updateProgressBar(progressBar, progress);
            });
            
            if (response.success) {
                toast.show('Document uploaded successfully!', 'success');
                setTimeout(() => {
                    window.location.href = '/modules/document-management/views/index.php';
                }, 1500);
            } else {
                toast.show(response.message || 'Upload failed', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-cloud-upload mr-2"></i>Upload Document';
                progressBar.remove();
            }
        } catch (error) {
            console.error('Upload error:', error);
            toast.show('Upload failed. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-cloud-upload mr-2"></i>Upload Document';
            progressBar.remove();
        }
    });
}

function createProgressBar() {
    const container = document.createElement('div');
    container.className = 'mt-4 bg-white rounded-lg shadow-md p-4';
    container.innerHTML = `
        <div class="mb-2 flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">Uploading document...</span>
            <span class="text-sm font-medium text-blue-600" id="upload-percentage">0%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div id="upload-progress" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
    `;
    return container;
}

function updateProgressBar(container, percentage) {
    const progressBar = container.querySelector('#upload-progress');
    const percentageText = container.querySelector('#upload-percentage');
    
    if (progressBar) {
        progressBar.style.width = percentage + '%';
    }
    if (percentageText) {
        percentageText.textContent = Math.round(percentage) + '%';
    }
}

// Auto-fill reference number based on document type
document.getElementById('document-type')?.addEventListener('change', function() {
    const refNumber = document.getElementById('reference-number');
    if (!refNumber || refNumber.value) return;
    
    const typePrefix = {
        'ordinance': 'ORD',
        'resolution': 'RES',
        'session': 'SS',
        'agenda': 'AGD',
        'committee': 'COM',
        'hearing': 'HRG',
        'consultation': 'CST',
        'research': 'RSC'
    };
    
    const prefix = typePrefix[this.value];
    if (prefix) {
        const year = new Date().getFullYear();
        refNumber.value = `${prefix}-${year}-`;
        refNumber.focus();
    }
});

console.log('Upload JS Loaded');
