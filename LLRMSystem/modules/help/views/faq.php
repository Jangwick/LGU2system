<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

$pageTitle = 'FAQ';
$currentPage = 'help';
include_once __DIR__ . '/../../core/layouts/header.php';
?>

<script>
    // Force light mode for help pages when viewed as guest
    if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
        document.documentElement.classList.remove('dark');
        document.documentElement.style.colorScheme = 'light';
    }
</script>

<?php if (isset($_SESSION['user_id'])): ?>
    <?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>
    <div class="flex-1 flex flex-col overflow-hidden bg-white">
        <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
<?php else: ?>
    <div class="flex-1 flex flex-col min-h-screen bg-gray-50">
        <!-- Minimal landing navbar for guests -->
        <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-gray-100 px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" class="h-8 w-8">
                <span class="font-black text-xl tracking-tighter text-gray-900 uppercase">VALENZUELA<span class="text-red-600">LRMS</span></span>
            </div>
            <a href="<?php echo BASE_URL; ?>/index.php#legal-footer" onclick="if(window.history.length > 1){ window.history.back(); return false; }" class="text-[10px] font-black text-gray-400 hover:text-red-600 uppercase tracking-[0.2em] transition-all">
                <i class="bi bi-arrow-left mr-2"></i>BACK TO HOME
            </a>
        </nav>
<?php endif; ?>

    
    <main class="flex-1 overflow-y-auto bg-gray-50 p-4 md:p-6 pb-20">

        <div class="max-w-4xl mx-auto">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-8 mb-8 text-white">
                <div class="text-center">
                    <i class="bi bi-patch-question text-5xl mb-4 block"></i>
                    <h1 class="text-3xl font-black mb-2">Frequently Asked Questions</h1>
                    <p class="text-red-100 text-sm">Everything you need to know about the Valenzuela LRMS.</p>
                </div>
            </div>

            <!-- FAQ Accordion -->
            <div class="space-y-4" id="faq-list">
                <!-- FAQ 1 -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 transition-all duration-300 hover:shadow-md">
                    <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-6 text-left group">
                        <span class="text-lg font-black text-gray-900 uppercase tracking-tight group-hover:text-red-600 transition-colors">What is the Valenzuela LRMS?</span>
                        <div class="h-8 w-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-red-50 group-hover:text-red-600 transition-all">
                            <i class="bi bi-plus-lg text-lg transition-transform duration-300"></i>
                        </div>
                    </button>
                    <div class="hidden px-6 pb-6">
                        <p class="text-gray-500 font-medium leading-relaxed uppercase text-[11px] tracking-widest">The Legislative Records Management System (LRMS) is a centralized digital portal designed to manage, store, and provide access to the official ordinances, resolutions, and legislative documents of the City Government of Valenzuela. It streamlines document retrieval and ensures transparency in governance.</p>
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 transition-all duration-300 hover:shadow-md">
                    <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-6 text-left group">
                        <span class="text-lg font-black text-gray-900 uppercase tracking-tight group-hover:text-red-600 transition-colors">Who can access the official records?</span>
                        <div class="h-8 w-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-red-50 group-hover:text-red-600 transition-all">
                            <i class="bi bi-plus-lg text-lg transition-transform duration-300"></i>
                        </div>
                    </button>
                    <div class="hidden px-6 pb-6">
                        <p class="text-gray-500 font-medium leading-relaxed uppercase text-[11px] tracking-widest">Public records like approved ordinances and resolutions are available for public viewing. However, restricted internal documents, sensitive drafts, and administrative management features are reserved for authorized city government personnel with verified credentials.</p>
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 transition-all duration-300 hover:shadow-md">
                    <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-6 text-left group">
                        <span class="text-lg font-black text-gray-900 uppercase tracking-tight group-hover:text-red-600 transition-colors">How do I search for a specific ordinance?</span>
                        <div class="h-8 w-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-red-50 group-hover:text-red-600 transition-all">
                            <i class="bi bi-plus-lg text-lg transition-transform duration-300"></i>
                        </div>
                    </button>
                    <div class="hidden px-6 pb-6">
                        <p class="text-gray-500 font-medium leading-relaxed uppercase text-[11px] tracking-widest">You can use our Advanced Search feature to filter documents by category, sequence number, year, or specific keywords. Once found, documents can be previewed or downloaded as official PDFs depending on your access level.</p>
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 transition-all duration-300 hover:shadow-md">
                    <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-6 text-left group">
                        <span class="text-lg font-black text-gray-900 uppercase tracking-tight group-hover:text-red-600 transition-colors">Is the portal available 24/7?</span>
                        <div class="h-8 w-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-red-50 group-hover:text-red-600 transition-all">
                            <i class="bi bi-plus-lg text-lg transition-transform duration-300"></i>
                        </div>
                    </button>
                    <div class="hidden px-6 pb-6">
                        <p class="text-gray-500 font-medium leading-relaxed uppercase text-[11px] tracking-widest">Yes. The Valenzuela LRMS is a web-based platform accessible anytime, anywhere. This digital transformation ensures that legislative information is always within reach of the citizens and city officials, reducing the need for physical office visits.</p>
                    </div>
                </div>

                <!-- FAQ 5 -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 transition-all duration-300 hover:shadow-md">
                    <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-6 text-left group">
                        <span class="text-lg font-black text-gray-900 uppercase tracking-tight group-hover:text-red-600 transition-colors">Who manages this digital system?</span>
                        <div class="h-8 w-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-red-50 group-hover:text-red-600 transition-all">
                            <i class="bi bi-plus-lg text-lg transition-transform duration-300"></i>
                        </div>
                    </button>
                    <div class="hidden px-6 pb-6">
                        <p class="text-gray-500 font-medium leading-relaxed uppercase text-[11px] tracking-widest">The system is maintained by the Legislative Records Department under the City Government of Valenzuela. Technical support and system security are managed by the City Information and Communications Technology (ICT) office.</p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-12 mb-8">
                <a href="<?php echo HELP_URL; ?>/views/index.php" class="text-red-600 hover:text-red-700 font-bold text-sm">
                    <i class="bi bi-arrow-left mr-1"></i> Back to Help & Support
                </a>
            </div>

        </div>
    </main>

    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<script>
function toggleFaq(button) {
    const content = button.nextElementSibling;
    const icon = button.querySelector('i');
    const isHidden = content.classList.contains('hidden');

    // Close all other FAQs in this list
    const allFaqs = document.querySelectorAll('#faq-list .bg-white > div');
    const allIcons = document.querySelectorAll('#faq-list .bg-white i.bi-dash-lg');
    
    allFaqs.forEach(faq => {
        if (!faq.classList.contains('hidden')) {
            faq.classList.add('hidden');
        }
    });
    
    allIcons.forEach(i => {
        i.classList.remove('bi-dash-lg');
        i.classList.add('bi-plus-lg');
        i.classList.remove('rotate-45');
    });

    if (isHidden) {
        content.classList.remove('hidden');
        icon.classList.remove('bi-plus-lg');
        icon.classList.add('bi-dash-lg');
        icon.classList.add('rotate-45');
    }
}
</script>
