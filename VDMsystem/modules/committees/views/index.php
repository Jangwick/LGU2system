<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';
require_once __DIR__ . '/../controllers/CommitteeController.php';

// Check authentication & admin access
if (!isset($_SESSION['user_id'])) {
    redirectToLogin();
}
if (!isAdmin()) {
    $_SESSION['flash_error'] = 'Access denied. Admin privileges required.';
    redirectToDashboard();
}

$controller = new CommitteeController();
$data = $controller->index();
$stats = $controller->getStatistics();

$pageTitle = 'Committee Management';
$currentPage = 'committees';
$breadcrumbs = [
    ['label' => 'Administration', 'url' => '#'],
    ['label' => 'Committees']
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
    <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-gray-950 p-3 md:p-6 custom-scrollbar">
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4 animate-fade-in">
                <div class="flex items-center">
                    <i class="bi bi-check-circle text-green-500 mr-2 text-lg"></i>
                    <span class="text-green-700 dark:text-green-300 font-medium"><?php echo $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Header Banner -->
        <div class="vdm-welcome-banner rounded-lg md:rounded-2xl shadow-xl p-4 md:p-7 mb-6 text-white transform transition-all duration-500 ease-in-out animate-fade-in relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl transition-opacity duration-500 dark:opacity-5"></div>
            
            <div class="relative flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-3xl font-black mb-1 tracking-tight transition-all duration-500">
                        Committees
                    </h1>
                    <p class="text-red-100 text-xs md:text-sm opacity-90 font-medium transition-all duration-500">
                        Manage legislative committees and their assignments.
                    </p>
                </div>
                
                <div class="shrink-0">
                    <button type="button" onclick="openCreateModal()" class="!bg-white !text-red-600 hover:!bg-gray-50 px-6 py-2.5 rounded-xl font-bold shadow-lg transition-all duration-500 transform hover:-translate-y-0.5 flex items-center group border border-red-600">
                        <i class="bi bi-plus-lg mr-2 transition-transform group-hover:rotate-90"></i>
                        New Committee
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 animate-fade-in-up">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-red-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Total</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white"><?php echo $stats['total']; ?></p>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-full p-2.5">
                        <i class="bi bi-people-fill text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-green-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Active</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo $stats['active']; ?></p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-full p-2.5">
                        <i class="bi bi-check-circle-fill text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-blue-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Documents</p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo $stats['total_documents']; ?></p>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-full p-2.5">
                        <i class="bi bi-file-earmark-text-fill text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-4 border-l-4 border-purple-500 hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider">Sessions</p>
                        <p class="text-2xl font-bold text-purple-600"><?php echo $stats['total_sessions']; ?></p>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-full p-2.5">
                        <i class="bi bi-calendar-event-fill text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search / Filter -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md p-6 mb-6 animate-fade-in-up" style="animation-delay: 100ms;">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="relative">
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1 block">Search</label>
                    <div class="relative">
                        <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="<?php echo e($data['filters']['search'] ?? ''); ?>" 
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all outline-none" 
                               placeholder="Search committees...">
                    </div>
                </div>
                
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1 block">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition-all">
                        <option value="">All</option>
                        <option value="1" <?php echo ($data['filters']['status'] ?? '') === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo ($data['filters']['status'] ?? '') === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-red-700 dark:bg-red-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-red-800 transition-all flex items-center justify-center shadow-sm">
                        <i class="bi bi-filter mr-2"></i> Filter
                    </button>
                    <a href="index.php" class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-all border border-gray-200 dark:border-gray-700" title="Clear">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Committees Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fade-in-up" style="animation-delay: 200ms;">
            <?php if (empty($data['committees'])): ?>
                <div class="col-span-full text-center py-16 bg-white dark:bg-gray-900 rounded-xl shadow-md">
                    <i class="bi bi-people text-gray-300 dark:text-gray-600 text-6xl block mb-4"></i>
                    <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">No committees found</p>
                    <button onclick="openCreateModal()" class="mt-4 text-red-600 font-bold hover:underline">Create your first committee</button>
                </div>
            <?php else: ?>
                <?php foreach ($data['committees'] as $committee): ?>
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-md border border-gray-100 dark:border-gray-800 hover:shadow-lg transition-all transform hover:-translate-y-1 group overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center group-hover:bg-red-600 group-hover:text-white transition-all">
                                        <i class="bi bi-people-fill text-red-600 text-xl group-hover:text-white"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 dark:text-white group-hover:text-red-700 dark:group-hover:text-red-400 transition-colors"><?php echo e($committee['name']); ?></h3>
                                        <?php if ($committee['is_active']): ?>
                                            <span class="text-xs font-bold text-green-600 dark:text-green-400">Active</span>
                                        <?php else: ?>
                                            <span class="text-xs font-bold text-red-600 dark:text-red-400">Inactive</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($committee['description'])): ?>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2"><?php echo e($committee['description']); ?></p>
                            <?php endif; ?>
                            
                            <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex items-center gap-1">
                                    <i class="bi bi-file-earmark-text text-blue-500"></i>
                                    <span><?php echo $committee['document_count'] ?? 0; ?> documents</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="bi bi-calendar-check text-purple-500"></i>
                                    <span><?php echo $committee['session_count'] ?? 0; ?> sessions</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="px-6 py-3 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-2">
                            <button type="button" onclick='editCommittee(<?php echo json_encode($committee); ?>)' 
                               class="px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-amber-600 dark:hover:text-amber-400 transition-colors" title="Edit">
                                <i class="bi bi-pencil-fill mr-1"></i> Edit
                            </button>
                            <?php if (($committee['document_count'] ?? 0) == 0 && ($committee['session_count'] ?? 0) == 0): ?>
                            <button type="button" onclick="deleteCommittee(<?php echo $committee['id']; ?>, '<?php echo e($committee['name']); ?>')" 
                               class="px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 transition-colors" title="Delete">
                                <i class="bi bi-trash-fill mr-1"></i> Delete
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<!-- Create/Edit Committee Modal -->
<div id="committeeModal" class="hidden fixed inset-0 bg-gray-900/50 dark:bg-black/70 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
    <div class="relative top-1/4 mx-auto p-5 border border-gray-200 dark:border-gray-700/50 w-full max-w-lg shadow-2xl rounded-2xl bg-white dark:bg-gray-900 mb-20">
        <div class="flex justify-between items-center mb-6">
            <h3 id="committeeModalTitle" class="text-xl font-bold text-gray-900 dark:text-white">Add New Committee</h3>
            <button onclick="closeCommitteeModal()" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        
        <form id="committeeForm" onsubmit="saveCommittee(event)">
            <input type="hidden" id="committeeId" name="id">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Committee Name <span class="text-red-500">*</span></label>
                <input type="text" id="committeeName" name="name" required 
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all"
                       placeholder="e.g. Finance and Budget Committee">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                <textarea id="committeeDescription" name="description" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all resize-none"
                          placeholder="Brief description of the committee's purpose..."></textarea>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select id="committeeStatus" name="is_active" 
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeCommitteeModal()" class="px-6 py-2.5 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg font-semibold transition-all">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition-all shadow-md hover:shadow-lg">
                    <i class="bi bi-save mr-2"></i> Save Committee
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('committeeModalTitle').textContent = 'Add New Committee';
    document.getElementById('committeeForm').reset();
    document.getElementById('committeeId').value = '';
    document.getElementById('committeeModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCommitteeModal() {
    document.getElementById('committeeModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function editCommittee(committee) {
    document.getElementById('committeeModalTitle').textContent = 'Edit Committee';
    document.getElementById('committeeId').value = committee.id;
    document.getElementById('committeeName').value = committee.name || '';
    document.getElementById('committeeDescription').value = committee.description || '';
    document.getElementById('committeeStatus').value = committee.is_active ? '1' : '0';
    document.getElementById('committeeModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function saveCommittee(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const id = document.getElementById('committeeId').value;
    const url = id ? App.apiUrl('committees', 'update-committee.php') : App.apiUrl('committees', 'create-committee.php');
    
    fetch(url, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Success!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast('Error: ' + (data.error || 'Operation failed'), 'error');
        }
    })
    .catch(err => showToast('Network error: ' + err.message, 'error'));
}

function deleteCommittee(id, name) {
    if (!confirm(`Are you sure you want to delete committee "${name}"?`)) return;
    
    fetch(App.apiUrl('committees', 'delete-committee.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Committee deleted!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast('Error: ' + (data.error || 'Delete failed'), 'error');
        }
    })
    .catch(err => showToast('Network error: ' + err.message, 'error'));
}

window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeCommitteeModal(); });
document.getElementById('committeeModal')?.addEventListener('click', function(e) { if (e.target === this) closeCommitteeModal(); });
</script>
