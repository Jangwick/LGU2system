<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

$pageTitle = 'Help & Support';
$currentPage = 'help';
include_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-8 mb-6 text-white transform hover:scale-[1.01] transition-all duration-300 animate-fade-in">
                <div class="text-center">
                    <i class="bi bi-headset text-6xl mb-4 animate-bounce-in"></i>
                    <h1 class="text-3xl font-bold mb-2 animate-slide-in-left animation-delay-100">Help & Support Center</h1>
                    <p class="text-red-100 text-lg animate-slide-in-left animation-delay-200">We're here to help you navigate the LRMS system</p>
                </div>
            </div>
            
            <!-- Search Bar -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-100">
                <div class="flex gap-3">
                    <div class="flex-1 relative">
                        <input type="text" 
                               id="help-search" 
                               placeholder="Search for help articles, guides, or FAQs..."
                               class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="bi bi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
                    </div>
                    <button class="btn-primary px-6">
                        Search
                    </button>
                </div>
            </div>
            
            <!-- Quick Help Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 cursor-pointer transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-200 group">
                    <div class="text-center">
                        <div class="bg-red-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                            <i class="bi bi-book text-red-600 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2 group-hover:text-red-600 transition-colors">User Guide</h3>
                        <p class="text-sm text-gray-600 mb-4">Step-by-step instructions for using LRMS</p>
                        <button onclick="showUserGuide()" class="text-red-600 hover:text-red-700 font-medium text-sm">
                            Learn More <i class="bi bi-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 cursor-pointer transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-300 group">
                    <div class="text-center">
                        <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                            <i class="bi bi-play-circle text-green-600 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2 group-hover:text-green-600 transition-colors">Video Tutorials</h3>
                        <p class="text-sm text-gray-600 mb-4">Watch video guides and walkthroughs</p>
                        <button onclick="showVideoTutorials()" class="text-green-600 hover:text-green-700 font-medium text-sm">
                            Watch Now <i class="bi bi-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 cursor-pointer transform hover:-translate-y-1 hover:scale-105 animate-fade-in-up animation-delay-400 group">
                    <div class="text-center">
                        <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                            <i class="bi bi-chat-dots text-purple-600 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2 group-hover:text-purple-600 transition-colors">Contact Support</h3>
                        <p class="text-sm text-gray-600 mb-4">Get in touch with our support team</p>
                        <button onclick="openContactModal()" class="text-purple-600 hover:text-purple-700 font-medium text-sm">
                            Contact Us <i class="bi bi-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- FAQs -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-500">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="bi bi-question-circle mr-2 text-red-600"></i>
                            Frequently Asked Questions
                        </h2>
                        
                        <div class="space-y-3">
                            <!-- FAQ Item -->
                            <div class="border border-gray-200 rounded-lg">
                                <button onclick="toggleFAQ(1)" class="w-full text-left p-4 flex items-center justify-between hover:bg-gray-50">
                                    <span class="font-medium text-gray-800">How do I upload a document?</span>
                                    <i class="bi bi-chevron-down text-gray-400" id="faq-icon-1"></i>
                                </button>
                                <div id="faq-content-1" class="hidden p-4 pt-0 text-sm text-gray-600">
                                    <p>To upload a document:</p>
                                    <ol class="list-decimal list-inside mt-2 space-y-1">
                                        <li>Navigate to Document Management</li>
                                        <li>Click "Upload Document" button</li>
                                        <li>Fill in the required information</li>
                                        <li>Select your file to upload</li>
                                        <li>Click "Submit" to save</li>
                                    </ol>
                                </div>
                            </div>
                            
                            <div class="border border-gray-200 rounded-lg">
                                <button onclick="toggleFAQ(2)" class="w-full text-left p-4 flex items-center justify-between hover:bg-gray-50">
                                    <span class="font-medium text-gray-800">How do I search for documents?</span>
                                    <i class="bi bi-chevron-down text-gray-400" id="faq-icon-2"></i>
                                </button>
                                <div id="faq-content-2" class="hidden p-4 pt-0 text-sm text-gray-600">
                                    <p>You can search for documents in two ways:</p>
                                    <ul class="list-disc list-inside mt-2 space-y-1">
                                        <li>Use the quick search in the top navigation bar</li>
                                        <li>Go to Advanced Search for more filters (type, date, department, etc.)</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="border border-gray-200 rounded-lg">
                                <button onclick="toggleFAQ(3)" class="w-full text-left p-4 flex items-center justify-between hover:bg-gray-50">
                                    <span class="font-medium text-gray-800">How do I change my password?</span>
                                    <i class="bi bi-chevron-down text-gray-400" id="faq-icon-3"></i>
                                </button>
                                <div id="faq-content-3" class="hidden p-4 pt-0 text-sm text-gray-600">
                                    <p>To change your password:</p>
                                    <ol class="list-decimal list-inside mt-2 space-y-1">
                                        <li>Click your profile icon in the top right</li>
                                        <li>Select "My Profile"</li>
                                        <li>Click "Change Password" under Account Security</li>
                                        <li>Enter your current and new password</li>
                                        <li>Click "Update Password"</li>
                                    </ol>
                                </div>
                            </div>
                            
                            <div class="border border-gray-200 rounded-lg">
                                <button onclick="toggleFAQ(4)" class="w-full text-left p-4 flex items-center justify-between hover:bg-gray-50">
                                    <span class="font-medium text-gray-800">What file types are supported?</span>
                                    <i class="bi bi-chevron-down text-gray-400" id="faq-icon-4"></i>
                                </button>
                                <div id="faq-content-4" class="hidden p-4 pt-0 text-sm text-gray-600">
                                    <p>The system supports the following file types:</p>
                                    <ul class="list-disc list-inside mt-2 space-y-1">
                                        <li>PDF (.pdf)</li>
                                        <li>Microsoft Word (.doc, .docx)</li>
                                        <li>Microsoft Excel (.xls, .xlsx)</li>
                                        <li>Microsoft PowerPoint (.ppt, .pptx)</li>
                                    </ul>
                                    <p class="mt-2">Maximum file size: 50MB</p>
                                </div>
                            </div>
                            
                            <div class="border border-gray-200 rounded-lg">
                                <button onclick="toggleFAQ(5)" class="w-full text-left p-4 flex items-center justify-between hover:bg-gray-50">
                                    <span class="font-medium text-gray-800">How do I generate reports?</span>
                                    <i class="bi bi-chevron-down text-gray-400" id="faq-icon-5"></i>
                                </button>
                                <div id="faq-content-5" class="hidden p-4 pt-0 text-sm text-gray-600">
                                    <p>To generate reports (Administrators and Officers only):</p>
                                    <ol class="list-decimal list-inside mt-2 space-y-1">
                                        <li>Navigate to Reports & Analytics</li>
                                        <li>View the dashboard for various charts and statistics</li>
                                        <li>Click "Export Reports" to download specific reports</li>
                                        <li>Select report type and date range</li>
                                        <li>Click "Export CSV" to download</li>
                                    </ol>
                                </div>
                            </div>
                            
                            <div class="border border-gray-200 rounded-lg">
                                <button onclick="toggleFAQ(6)" class="w-full text-left p-4 flex items-center justify-between hover:bg-gray-50">
                                    <span class="font-medium text-gray-800">What are document permissions?</span>
                                    <i class="bi bi-chevron-down text-gray-400" id="faq-icon-6"></i>
                                </button>
                                <div id="faq-content-6" class="hidden p-4 pt-0 text-sm text-gray-600">
                                    <p>The system has four user roles with different permissions:</p>
                                    <ul class="list-disc list-inside mt-2 space-y-1">
                                        <li><strong>Administrator:</strong> Full system access, can manage users and settings</li>
                                        <li><strong>Officer:</strong> Can create, edit, approve, and delete documents</li>
                                        <li><strong>Staff:</strong> Can create and edit own documents, view all documents</li>
                                        <li><strong>Viewer:</strong> Can only view and search documents</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Helpful Resources -->
                    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-600">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="bi bi-journal-text mr-2 text-red-600"></i>
                            Helpful Resources
                        </h2>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <a href="#" class="p-4 border border-gray-200 rounded-lg hover:bg-red-50 hover:border-red-300 transition">
                                <i class="bi bi-file-pdf text-red-600 text-2xl mb-2"></i>
                                <h3 class="font-medium text-gray-800 mb-1">User Manual (PDF)</h3>
                                <p class="text-sm text-gray-600">Complete system documentation</p>
                            </a>
                            
                            <a href="#" class="p-4 border border-gray-200 rounded-lg hover:bg-red-50 hover:border-red-300 transition">
                                <i class="bi bi-laptop text-red-600 text-2xl mb-2"></i>
                                <h3 class="font-medium text-gray-800 mb-1">Quick Start Guide</h3>
                                <p class="text-sm text-gray-600">Get started in 5 minutes</p>
                            </a>
                            
                            <a href="#" class="p-4 border border-gray-200 rounded-lg hover:bg-red-50 hover:border-red-300 transition">
                                <i class="bi bi-keyboard text-purple-600 text-2xl mb-2"></i>
                                <h3 class="font-medium text-gray-800 mb-1">Keyboard Shortcuts</h3>
                                <p class="text-sm text-gray-600">Work faster with shortcuts</p>
                            </a>
                            
                            <a href="#" class="p-4 border border-gray-200 rounded-lg hover:bg-red-50 hover:border-red-300 transition">
                                <i class="bi bi-shield-check text-green-600 text-2xl mb-2"></i>
                                <h3 class="font-medium text-gray-800 mb-1">Security Best Practices</h3>
                                <p class="text-sm text-gray-600">Keep your account secure</p>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Contact Information -->
                    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 animate-fade-in-up animation-delay-700">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i class="bi bi-telephone mr-2 text-red-600"></i>
                            Contact Information
                        </h2>
                        
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-envelope text-red-600 text-xl mt-1"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Email Support</p>
                                    <a href="mailto:support@lgu.gov.ph" class="text-sm text-red-600 hover:text-red-700">
                                        support@lgu.gov.ph
                                    </a>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-3">
                                <i class="bi bi-telephone text-red-600 text-xl mt-1"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Phone Support</p>
                                    <a href="tel:+6328888888" class="text-sm text-red-600 hover:text-red-700">
                                        (02) 8888-8888
                                    </a>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-3">
                                <i class="bi bi-clock text-blue-600 text-xl mt-1"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Business Hours</p>
                                    <p class="text-sm text-gray-600">Monday - Friday</p>
                                    <p class="text-sm text-gray-600">8:00 AM - 5:00 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- System Status -->
                    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animation-delay-800 hover:shadow-xl transition-all duration-300">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i class="bi bi-activity mr-2 text-blue-600"></i>
                            System Status
                        </h2>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">All Systems</span>
                                <span class="badge badge-success">
                                    <i class="bi bi-check-circle mr-1"></i>Operational
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">API</span>
                                <span class="badge badge-success">
                                    <i class="bi bi-check-circle mr-1"></i>Online
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Database</span>
                                <span class="badge badge-success">
                                    <i class="bi bi-check-circle mr-1"></i>Healthy
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Storage</span>
                                <span class="badge badge-success">
                                    <i class="bi bi-check-circle mr-1"></i>Available
                                </span>
                            </div>
                        </div>
                        
                        <a href="#" class="block mt-4 text-sm text-blue-600 hover:text-blue-700 text-center">
                            View Status Page <i class="bi bi-arrow-right ml-1"></i>
                        </a>
                    </div>
                    
                    <!-- Submit Feedback -->
                    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-md p-6 text-white animate-fade-in-up animation-delay-900 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                        <h2 class="text-lg font-bold mb-2">Have Feedback?</h2>
                        <p class="text-sm text-red-100 mb-4">Help us improve LRMS by sharing your thoughts</p>
                        <button onclick="openFeedbackModal()" class="w-full bg-white text-red-600 font-semibold py-2 px-4 rounded-lg hover:bg-red-50 transition">
                            <i class="bi bi-chat-square-text mr-2"></i>Submit Feedback
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<!-- Contact Support Modal -->
<div id="contactModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-900">Contact Support</h3>
            <button onclick="closeContactModal()" class="text-gray-400 hover:text-gray-600">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        
        <form id="contactForm" class="space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Your Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>" class="input-field" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>" class="input-field" required>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select name="category" class="input-field" required>
                    <option value="">Select category...</option>
                    <option value="technical">Technical Issue</option>
                    <option value="account">Account Problem</option>
                    <option value="document">Document Management</option>
                    <option value="feature">Feature Request</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                <input type="text" name="subject" class="input-field" required>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                <textarea name="message" rows="6" class="input-field" placeholder="Describe your issue or question in detail..." required></textarea>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeContactModal()" class="btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn-primary">
                    <i class="bi bi-send mr-2"></i>Send Message
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Feedback Modal -->
<div id="feedbackModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-900">Submit Feedback</h3>
            <button onclick="closeFeedbackModal()" class="text-gray-400 hover:text-gray-600">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        
        <form id="feedbackForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">How satisfied are you with LRMS?</label>
                <div class="flex justify-center gap-4 p-4">
                    <button type="button" onclick="setRating(1)" class="rating-btn text-4xl hover:scale-125 transition">😞</button>
                    <button type="button" onclick="setRating(2)" class="rating-btn text-4xl hover:scale-125 transition">😐</button>
                    <button type="button" onclick="setRating(3)" class="rating-btn text-4xl hover:scale-125 transition">🙂</button>
                    <button type="button" onclick="setRating(4)" class="rating-btn text-4xl hover:scale-125 transition">😊</button>
                    <button type="button" onclick="setRating(5)" class="rating-btn text-4xl hover:scale-125 transition">😍</button>
                </div>
                <input type="hidden" name="rating" id="rating" required>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Your Feedback</label>
                <textarea name="feedback" rows="4" class="input-field" placeholder="Share your thoughts, suggestions, or concerns..." required></textarea>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeFeedbackModal()" class="btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn-primary">
                    <i class="bi bi-check-circle mr-2"></i>Submit Feedback
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleFAQ(id) {
    const content = document.getElementById(`faq-content-${id}`);
    const icon = document.getElementById(`faq-icon-${id}`);
    
    content.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}

function showUserGuide() {
    alert('User guide feature coming soon!');
}

function showVideoTutorials() {
    alert('Video tutorials feature coming soon!');
}

function openContactModal() {
    document.getElementById('contactModal').classList.remove('hidden');
}

function closeContactModal() {
    document.getElementById('contactModal').classList.add('hidden');
}

function openFeedbackModal() {
    document.getElementById('feedbackModal').classList.remove('hidden');
}

function closeFeedbackModal() {
    document.getElementById('feedbackModal').classList.add('hidden');
}

function setRating(rating) {
    document.getElementById('rating').value = rating;
    document.querySelectorAll('.rating-btn').forEach(btn => btn.classList.remove('selected'));
    event.target.classList.add('selected');
}

// Contact Form Handler
document.getElementById('contactForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    alert('Thank you for contacting us! We will respond to your inquiry shortly.');
    closeContactModal();
    this.reset();
});

// Feedback Form Handler
document.getElementById('feedbackForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    if (!formData.get('rating')) {
        alert('Please select a rating!');
        return;
    }
    
    alert('Thank you for your feedback! Your input helps us improve LRMS.');
    closeFeedbackModal();
    this.reset();
});
</script>

<style>
.rating-btn.selected {
    transform: scale(1.25);
    filter: drop-shadow(0 0 10px rgba(59, 130, 246, 0.5));
}

.rotate-180 {
    transform: rotate(180deg);
    transition: transform 0.3s ease;
}
</style>
