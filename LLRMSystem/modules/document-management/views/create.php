<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

// Check if user has permission to upload documents (not viewer)
$userRole = strtolower(trim($_SESSION['user_role'] ?? 'viewer'));
if ($userRole === 'viewer') {
    require_once __DIR__ . '/../../core/config/config.php';
    $_SESSION['error_message'] = 'Access denied. Viewers cannot upload documents.';
    redirect(DOCUMENTS_INDEX_URL);
}

$pageTitle = 'Upload Document';
$currentPage = 'documents-create';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => DASHBOARD_INDEX_URL],
    ['label' => 'Documents', 'url' => DOCUMENTS_INDEX_URL],
    ['label' => 'Upload']
];

include_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-gray-100 p-3 sm:p-4 md:p-6">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-4 md:mb-6 animate-fade-in">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-1 sm:mb-2">Upload New Document</h1>
                <p class="text-sm sm:text-base text-gray-600">Add a new legislative document to the repository</p>
            </div>
            
            <!-- Upload Form -->
            <form id="upload-form" action="<?php echo DOCUMENTS_URL; ?>/api/upload.php" method="POST" enctype="multipart/form-data">
                <!-- File Upload Section -->
                <div class="bg-white rounded-xl shadow-md p-4 sm:p-5 md:p-6 mb-4 md:mb-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-100">
                    <h2 class="text-base sm:text-lg font-bold text-gray-800 mb-3 sm:mb-4 flex items-center">
                        <i class="bi bi-cloud-upload mr-2 text-blue-600"></i>
                        Document File
                    </h2>
                    
                    <!-- Drag & Drop Area -->
                    <div id="drop-zone" class="border-2 border-dashed border-gray-300 rounded-lg p-4 sm:p-6 md:p-8 text-center hover:border-blue-500 transition cursor-pointer">
                        <i class="bi bi-cloud-arrow-up text-4xl sm:text-5xl md:text-6xl text-gray-400 mb-3 sm:mb-4"></i>
                        <p class="text-base sm:text-lg font-medium text-gray-700 mb-1 sm:mb-2">Drag and drop your file here</p>
                        <p class="text-xs sm:text-sm text-gray-500 mb-3 sm:mb-4">or click to browse</p>
                        <input type="file" id="file-input" name="document_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" class="hidden" required>
                        <button type="button" onclick="document.getElementById('file-input').click()" class="btn-primary">
                            <i class="bi bi-folder2-open mr-2"></i>
                            Browse Files
                        </button>
                        <p class="text-xs text-gray-500 mt-4">
                            Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX (Max 50MB)
                        </p>
                    </div>
                    
                    <!-- File Preview -->
                    <div id="file-preview" class="hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="bi bi-file-earmark text-blue-600 text-2xl mr-3"></i>
                                <div>
                                    <p id="file-name" class="text-sm font-medium text-gray-800"></p>
                                    <p id="file-size" class="text-xs text-gray-600"></p>
                                </div>
                            </div>
                            <button type="button" id="remove-file" class="text-red-600 hover:text-red-700">
                                <i class="bi bi-x-circle text-xl"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Document Information -->
                <div class="bg-white rounded-xl shadow-md p-4 sm:p-5 md:p-6 mb-4 md:mb-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-200">
                    <h2 class="text-base sm:text-lg font-bold text-gray-800 mb-3 sm:mb-4 flex items-center">
                        <i class="bi bi-info-circle mr-2 text-blue-600"></i>
                        Document Information
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <!-- Document Type -->
                        <div>
                            <label for="document-type" class="block text-sm font-medium text-gray-700 mb-2">
                                Document Type <span class="text-red-500">*</span>
                            </label>
                            <select id="document-type" name="document_type" required class="input-field">
                                <option value="">Select Type</option>
                                <option value="ordinance">Ordinance</option>
                                <option value="resolution">Resolution</option>
                                <option value="session">Session Minutes</option>
                                <option value="agenda">Agenda</option>
                                <option value="committee">Committee Report</option>
                                <option value="hearing">Public Hearing</option>
                                <option value="consultation">Public Consultation</option>
                                <option value="research">Research Document</option>
                            </select>
                        </div>

                        <!-- Confidentiality Level -->
                        <div>
                            <label for="confidentiality-level" class="block text-sm font-medium text-gray-700 mb-2">
                                Confidentiality Level
                            </label>
                            <select id="confidentiality-level" name="confidentiality_level" class="input-field">
                                <option value="public" selected>Public</option>
                                <option value="internal">Internal</option>
                                <option value="confidential">Confidential</option>
                                <option value="restricted">Restricted</option>
                            </select>
                        </div>

                        <!-- Reference Number -->
                        <div>
                            <label for="reference-number" class="block text-sm font-medium text-gray-700 mb-2">
                                Reference Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="reference-number" 
                                   name="reference_number" 
                                   required
                                   placeholder="e.g., ORD-2025-042"
                                   class="input-field">
                        </div>
                        
                        <!-- Document Title -->
                        <div class="md:col-span-2">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                Document Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="title"
                                   name="title"
                                   required
                                   placeholder="Enter document title"
                                   class="input-field">
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="bi bi-info-circle mr-1"></i>
                                Recommended format: [Type]-[ReferenceNumber]: [Title] (e.g., Committee-COM-2026-001: Annual Report)
                            </p>
                        </div>
                        
                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="4" 
                                      placeholder="Brief description of the document"
                                      class="input-field"></textarea>
                        </div>
                        
                        <!-- Document Date -->
                        <div>
                            <label for="document-date" class="block text-sm font-medium text-gray-700 mb-2">
                                Document Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   id="document-date" 
                                   name="document_date" 
                                   required
                                   class="input-field">
                        </div>
                        
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select id="status" name="status" required class="input-field">
                                <option value="draft">Draft</option>
                                <option value="pending">Pending Review</option>
                                <option value="approved">Approved</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Tags & Categories -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="bi bi-tags mr-2 text-blue-600"></i>
                        Tags & Categories
                    </h2>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Category -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                Category
                            </label>
                            <select id="category" name="category" class="input-field">
                                <option value="">Select Category</option>
                                <option value="legislative">Legislative</option>
                                <option value="administrative">Administrative</option>
                                <option value="financial">Financial</option>
                                <option value="legal">Legal</option>
                                <option value="public-service">Public Service</option>
                            </select>
                        </div>
                        
                        <!-- Priority -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                                Priority
                            </label>
                            <select id="priority" name="priority" class="input-field">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        
                        <!-- Tags -->
                        <div class="md:col-span-2">
                            <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">
                                Tags (comma-separated)
                            </label>
                            <input type="text" 
                                   id="tags" 
                                   name="tags" 
                                   placeholder="e.g., budget, taxation, public works"
                                   class="input-field">
                            <p class="text-xs text-gray-500 mt-1">
                                Separate tags with commas for better organization and searchability
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Related Module (For Integration) -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="bi bi-link-45deg mr-2 text-blue-600"></i>
                        Module Integration
                    </h2>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Related Module -->
                        <div>
                            <label for="related-module" class="block text-sm font-medium text-gray-700 mb-2">
                                Related Module
                            </label>
                            <select id="related-module" name="related_module" class="input-field">
                                <option value="">None</option>
                                <option value="ordinances">Ordinances Management</option>
                                <option value="sessions">Sessions Management</option>
                                <option value="agendas">Agendas Management</option>
                                <option value="committees">Committees Management</option>
                                <option value="voting">Voting Records</option>
                                <option value="hearings">Public Hearings</option>
                                <option value="archives">Archives</option>
                                <option value="consultations">Consultations</option>
                                <option value="research">Research Library</option>
                            </select>
                        </div>
                        
                        <!-- External Reference ID -->
                        <div>
                            <label for="external-ref" class="block text-sm font-medium text-gray-700 mb-2">
                                External Reference ID
                            </label>
                            <input type="text" 
                                   id="external-ref" 
                                   name="external_reference_id" 
                                   placeholder="ID from related module"
                                   class="input-field">
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 mt-6">
                    <a href="index.php" class="btn-secondary text-center">
                        <i class="bi bi-x-circle mr-1 sm:mr-2"></i>Cancel
                    </a>
                    <button type="submit" id="submit-btn" class="flex-1 bg-red-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200 transform transition-all active:scale-95 shadow-lg">
                        <i class="bi bi-upload mr-2"></i>Upload Document
                    </button>
                    <button type="button" onclick="printForm()" class="flex-1 bg-gray-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 transform transition-all active:scale-95 shadow-lg">
                        <i class="bi bi-printer mr-2"></i>Print Form
                    </button>
                </div>
            </form>
        </div>
    </main>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<script src="/public/assets/js/upload.js"></script>
<script>
    // Print form function
    function printForm() {
        window.print();
    }

    // File input handling
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');
    const filePreview = document.getElementById('file-preview');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const removeFile = document.getElementById('remove-file');
    
    // Click to upload
    dropZone.addEventListener('click', () => fileInput.click());
    
    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    // Highlight drop zone when dragging over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        });
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        });
    });
    
    // Handle dropped files
    dropZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length) {
            fileInput.files = files;
            displayFile(files[0]);
        }
    });
    
    // Handle file selection
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length) {
            displayFile(e.target.files[0]);
        }
    });
    
    // Display selected file
    function displayFile(file) {
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        filePreview.classList.remove('hidden');
        dropZone.classList.add('hidden');
    }
    
    // Remove file
    removeFile.addEventListener('click', () => {
        fileInput.value = '';
        filePreview.classList.add('hidden');
        dropZone.classList.remove('hidden');
    });
    
    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
    
    // Set default date to today
    document.getElementById('document-date').valueAsDate = new Date();
    
    // Handle form submission
    const uploadForm = document.getElementById('upload-form');
    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Validate file is selected
        if (!fileInput.files.length) {
            alert('Please select a file to upload');
            return;
        }
        
        // Create FormData
        const formData = new FormData(uploadForm);
        
        // Show loading state
        const submitBtn = uploadForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split mr-2"></i>Uploading...';
        
        try {
            const response = await fetch(App.apiUrl('documents', 'upload.php'), {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Document uploaded successfully!');
                window.location.href = App.config.urls.documents + '/views/index.php';
            } else {
                alert('Error: ' + (result.error || 'Upload failed'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            alert('Network error: ' + error.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
</script>
