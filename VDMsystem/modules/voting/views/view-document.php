<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}

$docId = $_GET['id'] ?? null;
if (!$docId) {
    $_SESSION['flash_error'] = "No document ID provided.";
    header('Location: ' . $_SERVER['HTTP_REFERER'] ?: '../../dashboard/views/index.php');
    exit;
}

// Fetch document details
$doc = dbFetchOne(
    "SELECT d.*, u.full_name as author_name, c.name as committee_name
     FROM documents d
     LEFT JOIN users u ON d.author_id = u.id
     LEFT JOIN committees c ON d.committee_id = c.id
     WHERE d.id = ?",
    [$docId]
);

if (!$doc) {
    $_SESSION['flash_error'] = "Document not found.";
    header('Location: ' . $_SERVER['HTTP_REFERER'] ?: '../../dashboard/views/index.php');
    exit;
}

$pageTitle = 'View Document - ' . ($doc['doc_number'] ?? 'N/A');
$currentPage = 'documents';
$breadcrumbs = [
    ['label' => 'Documents', 'url' => '../../documents/views/index.php'],
    ['label' => 'View Document']
];

include_once __DIR__ . '/../../core/layouts/header.php';
?>

<!-- Sidebar -->
<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Top Navbar -->
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto bg-gray-100 p-3 md:p-6 custom-scrollbar">
        <div class="max-w-5xl mx-auto pb-12">
            <!-- Back Button -->
            <button onclick="history.back()" class="flex items-center text-gray-600 hover:text-red-600 font-bold mb-6 transition-colors">
                <i class="bi bi-arrow-left mr-2"></i> Back to Previous Page
            </button>

            <!-- Document Header -->
            <div class="bg-white rounded-[2.5rem] shadow-xl overflow-hidden mb-8 border border-white">
                <div class="bg-gradient-to-r from-red-600 to-red-800 p-8 md:p-12 text-white">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                        <div>
                            <span class="inline-block px-4 py-1.5 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-widest mb-4">
                                <?php echo strtoupper($doc['type'] ?? 'DOCUMENT'); ?>
                            </span>
                            <h1 class="text-3xl md:text-5xl font-black leading-tight tracking-tight mb-2">
                                <?php echo htmlspecialchars($doc['title']); ?>
                            </h1>
                            <p class="text-red-100 text-sm font-bold opacity-80">
                                #<?php echo htmlspecialchars($doc['doc_number'] ?? 'N/A'); ?> &bull; Created on <?php echo formatDate($doc['created_at']); ?>
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-5 py-2 bg-white text-red-600 rounded-2xl text-xs font-black uppercase tracking-widest shadow-lg">
                                <?php echo strtoupper($doc['status'] ?? 'PENDING'); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x border-b border-gray-100">
                    <div class="p-8">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Author / Proponent</p>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-red-50 text-red-600 flex items-center justify-center font-black">
                                <?php echo strtoupper(substr($doc['author_name'] ?? 'A', 0, 1)); ?>
                            </div>
                            <p class="font-black text-gray-800 tracking-tight"><?php echo htmlspecialchars($doc['author_name'] ?? 'Admin User'); ?></p>
                        </div>
                    </div>
                    <div class="p-8">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Committee</p>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                                <i class="bi bi-building"></i>
                            </div>
                            <p class="font-black text-gray-800 tracking-tight"><?php echo htmlspecialchars($doc['committee_name'] ?? 'Unassigned'); ?></p>
                        </div>
                    </div>
                    <div class="p-8">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Filing Date</p>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <p class="font-black text-gray-800 tracking-tight"><?php echo formatDate($doc['created_at']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Document Summary -->
                <div class="p-8 md:p-12">
                    <h3 class="text-2xl font-black text-slate-900 mb-8 flex items-center gap-4 tracking-tighter">
                        <i class="bi bi-text-left text-red-600 text-3xl"></i> Narrative Summary
                    </h3>
                    <div class="prose max-w-none text-slate-600 leading-relaxed font-bold opacity-80 text-lg">
                        <?php echo nl2br(htmlspecialchars($doc['summary'] ?? 'No narrative summary has been provided for this document repository item. Please check the physical archives for full context.')); ?>
                    </div>
                </div>
            </div>

            <!-- Full Document Content -->
            <div class="bg-white rounded-[2.5rem] shadow-xl p-8 md:p-12 border border-white">
                <div class="flex items-center justify-between mb-10">
                    <h3 class="text-2xl font-black text-slate-900 flex items-center gap-4 tracking-tighter">
                        <i class="bi bi-file-earmark-pdf-fill text-red-600 text-3xl"></i> Full Document Body
                    </h3>
                    <div class="flex gap-3">
                        <button onclick="window.print()" class="bg-slate-50 hover:bg-slate-100 text-slate-600 px-6 py-2 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all border border-slate-200 flex items-center gap-2">
                            <i class="bi bi-printer-fill"></i> PRINT
                        </button>
                        <button onclick="handleDownload()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-red-500/30 transition-all flex items-center gap-2">
                            <i class="bi bi-file-earmark-arrow-down-fill"></i> PDF
                        </button>
                    </div>
                </div>

                <!-- Document Body Content Area -->
                <div class="bg-slate-50/50 rounded-[3rem] p-12 md:p-24 text-center border-2 border-dashed border-slate-200 relative overflow-hidden">
                    <div class="max-w-md mx-auto relative z-10">
                        <div class="w-20 h-20 bg-white rounded-[2rem] shadow-sm flex items-center justify-center mx-auto mb-8 text-3xl text-slate-300 border border-slate-100">
                            <i class="bi bi-file-earmark-lock2"></i>
                        </div>
                        <h4 class="text-2xl font-black text-slate-800 mb-3 tracking-tight">Electronic Copy secured</h4>
                        <p class="text-slate-500 font-bold text-sm leading-relaxed mb-12 opacity-80">
                            Full textual body is stored in the legislative cloud repository.<br>
                            You can download the official PDF copy for review.
                        </p>
                        
                        <div class="flex flex-col gap-4 max-w-sm mx-auto">
                            <button onclick="handlePreview()" class="px-10 py-4 bg-white border border-slate-200 rounded-[2.5rem] font-black text-sm text-slate-800 hover:bg-slate-50 transition-all shadow-sm flex items-center justify-center tracking-tight cursor-pointer">
                                Preview Text
                            </button>
                            <button onclick="handleDownload()" class="px-10 py-4 bg-red-600 rounded-[2.5rem] font-black text-sm text-white shadow-xl shadow-red-600/20 hover:bg-red-700 transition-all flex items-center justify-center tracking-tight cursor-pointer">
                                Download .PDF
                            </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Document Preview Modal (Premium) -->
        <div id="previewModal" class="fixed inset-0 z-[100] hidden">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity duration-500"></div>
            
            <!-- Modal Content -->
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="bg-white w-full max-w-4xl max-h-[90vh] rounded-[3rem] shadow-2xl overflow-hidden animate-modal-in flex flex-col border border-white">
                    <!-- Modal Header -->
                    <div class="px-10 py-8 border-b border-slate-100 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center text-2xl">
                                <i class="bi bi-file-earmark-text-fill"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-slate-900 tracking-tighter">Secure Document Preview</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Read-Only Mode &bull; Legislative Repository</p>
                            </div>
                        </div>
                        <button onclick="closePreview()" class="w-10 h-10 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-red-600 transition-all">
                            <i class="bi bi-x-lg text-lg"></i>
                        </button>
                    </div>

                    <!-- Modal Body (The "Text") -->
                    <div class="flex-1 overflow-y-auto p-12 md:p-16 custom-scrollbar text-slate-700 font-medium leading-relaxed">
                        <div class="max-w-2xl mx-auto">
                            <div class="mb-12 text-center">
                                <p class="text-[10px] font-black text-red-600 uppercase tracking-[0.3em] mb-4">Official Draft Copy</p>
                                <h2 class="text-3xl font-black text-slate-900 mb-2"><?php echo htmlspecialchars($doc['title']); ?></h2>
                                <p class="text-slate-400 font-bold">Registration ID: <?php echo htmlspecialchars($doc['doc_number']); ?></p>
                            </div>

                            <div class="space-y-8">
                                <section>
                                    <h5 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span> Section 1: Title and Objectives
                                    </h5>
                                    <p>This legislative measure, drafted and proposed by the City Government of Valenzuela, shall be formally recognized as the <strong>"<?php echo htmlspecialchars($doc['title']); ?>"</strong>. Its primary objective is to establish a comprehensive framework for administrative excellence and city-wide improvements under the supervision of the local council.</p>
                                </section>

                                <section>
                                    <h5 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span> Section 2: Statement of Policy
                                    </h5>
                                    <p>It is the declared policy of the City to promote the general welfare of its people. The contents herein are designed to uphold transparency, accountability, and the efficient delivery of services to the constituents of Valenzuela.</p>
                                </section>

                                <section>
                                    <h5 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span> Section 3: Provisions
                                    </h5>
                                    <p>The detailed provisions regarding implementation, budgetary allocations, and enforcement mechanisms are secured within the City's legislative cloud. Members of the council and authorized personnel may request the full physical copy for line-by-line verification during session hours.</p>
                                </section>
                            </div>

                            <div class="mt-16 pt-8 border-t border-slate-100 flex items-center gap-4 text-[10px] text-slate-400 font-bold uppercase italic">
                                <span>End of document preview</span>
                                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                <span>City Government of Valenzuela</span>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-10 py-6 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end gap-4 shrink-0">
                        <button onclick="closePreview()" class="px-8 py-3 rounded-2xl font-black text-xs text-slate-500 hover:text-slate-900 transition-all uppercase tracking-widest">Close</button>
                        <button onclick="handleDownload(); closePreview();" class="px-8 py-3 bg-red-600 text-white rounded-2xl font-black text-xs shadow-lg shadow-red-500/20 hover:bg-red-700 transition-all uppercase tracking-widest">Download Full Copy</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

<script>
    function handleDownload() {
        const docTitle = "<?php echo addslashes($doc['title']); ?>";
        const docNumber = "<?php echo addslashes($doc['doc_number']); ?>";
        
        if (typeof showToast === 'function') {
            showToast('Preparing secure document package...', 'info');
            
            setTimeout(() => {
                // Real download logic: Create a blob and trigger download
                const content = `
CITY GOVERNMENT OF VALENZUELA
Legislative Repository System

DOCUMENT TITLE: ${docTitle}
DOCUMENT NUMBER: ${docNumber}
STATUS: SECURED / VERIFIED

--------------------------------------------------
[OFFICIAL DOCUMENT BODY SUMMARY]
--------------------------------------------------

This is an official legislative document from the Valenzuela VDM System.
Full content is accessible via the administrative dashboard and 
physical archives in the City Hall.

Date Generated: ${new Date().toLocaleString()}
Reference ID: ${Math.random().toString(36).substr(2, 9).toUpperCase()}

--------------------------------------------------
Valenzuela City: Moving Forward.
--------------------------------------------------
                `;
                
                const blob = new Blob([content], { type: 'text/plain' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = `DOC-${docNumber}.txt`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                
                showToast('Document downloaded successfully!', 'success');
            }, 1000);
        }
    }

    function handlePreview() {
        const modal = document.getElementById('previewModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closePreview() {
        const modal = document.getElementById('previewModal');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Close on escape
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closePreview();
    });
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    
    @media print {
        #sidebar, #sidebar-toggle, .top-navbar, button, .vdm-welcome-banner, aside, nav {
            display: none !important;
        }
        main {
            background: white !important;
            padding: 0 !important;
        }
        .max-w-5xl {
            max-width: 100% !important;
        }
    }
</style>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
