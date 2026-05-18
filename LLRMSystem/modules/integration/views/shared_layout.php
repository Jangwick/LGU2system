<?php
// Shared template for integration modules
$moduleTypes = [
    'ordinances' => ['title' => 'Integrated Ordinances', 'icon' => 'bi-journal-text', 'color' => 'blue'],
    'sessions' => ['title' => 'Integrated Sessions', 'icon' => 'bi-calendar3', 'color' => 'indigo'],
    'agendas' => ['title' => 'Integrated Agendas', 'icon' => 'bi-list-check', 'color' => 'purple'],
    'committees' => ['title' => 'Integrated Committees', 'icon' => 'bi-people', 'color' => 'teal'],
    'voting' => ['title' => 'Integrated Voting Records', 'icon' => 'bi-hand-thumbs-up', 'color' => 'green'],
    'hearings' => ['title' => 'Integrated Public Hearings', 'icon' => 'bi-megaphone', 'color' => 'orange'],
    'archives' => ['title' => 'Integrated Archives', 'icon' => 'bi-archive', 'color' => 'gray'],
    'consultations' => ['title' => 'Integrated Consultations', 'icon' => 'bi-chat-dots', 'color' => 'pink'],
    'research' => ['title' => 'Integrated Research', 'icon' => 'bi-book', 'color' => 'cyan']
];

$config = $moduleTypes[$currentModule] ?? $moduleTypes['ordinances'];
$controller = new IntegrationController();
$records = $controller->getRecords($currentModule);

// File icon helpers (with extension fallback)
function getIntFileTypeByExt($fileName) {
    $ext = strtolower(pathinfo($fileName ?: '', PATHINFO_EXTENSION));
    $map = ['pdf'=>'pdf','doc'=>'word','docx'=>'word','xls'=>'excel','xlsx'=>'excel','csv'=>'excel','ppt'=>'powerpoint','pptx'=>'powerpoint'];
    return $map[$ext] ?? null;
}

function getIntFileIcon($mimeType, $fileName = '') {
    if (!$mimeType && !$fileName) return 'bi bi-file-earmark text-gray-400';
    if ($mimeType && strpos($mimeType, 'pdf') !== false) return 'bi bi-file-pdf text-red-600';
    if ($mimeType && strpos($mimeType, 'word') !== false) return 'bi bi-file-word text-blue-600';
    if ($mimeType && (strpos($mimeType, 'excel') !== false || strpos($mimeType, 'spreadsheet') !== false)) return 'bi bi-file-excel text-green-600';
    if ($mimeType && (strpos($mimeType, 'powerpoint') !== false || strpos($mimeType, 'presentation') !== false)) return 'bi bi-file-ppt text-orange-600';
    $t = getIntFileTypeByExt($fileName);
    if ($t === 'pdf') return 'bi bi-file-pdf text-red-600';
    if ($t === 'word') return 'bi bi-file-word text-blue-600';
    if ($t === 'excel') return 'bi bi-file-excel text-green-600';
    if ($t === 'powerpoint') return 'bi bi-file-ppt text-orange-600';
    return 'bi bi-file-earmark text-gray-400';
}

function getIntFileIconBg($mimeType, $fileName = '') {
    if (!$mimeType && !$fileName) return 'bg-gray-100';
    if ($mimeType && strpos($mimeType, 'pdf') !== false) return 'bg-red-100';
    if ($mimeType && strpos($mimeType, 'word') !== false) return 'bg-blue-100';
    if ($mimeType && (strpos($mimeType, 'excel') !== false || strpos($mimeType, 'spreadsheet') !== false)) return 'bg-green-100';
    if ($mimeType && (strpos($mimeType, 'powerpoint') !== false || strpos($mimeType, 'presentation') !== false)) return 'bg-orange-100';
    $t = getIntFileTypeByExt($fileName);
    if ($t === 'pdf') return 'bg-red-100';
    if ($t === 'word') return 'bg-blue-100';
    if ($t === 'excel') return 'bg-green-100';
    if ($t === 'powerpoint') return 'bg-orange-100';
    return 'bg-gray-100';
}
?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php require_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
        <!-- Header -->
        <div class="bg-gradient-to-r from-red-700 to-red-900 rounded-2xl shadow-lg p-8 mb-6 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold flex items-center">
                        <i class="bi <?php echo $config['icon']; ?> mr-3"></i>
                        <?php echo $config['title']; ?>
                    </h1>
                    <p class="text-red-100 mt-2 opacity-90">External data automatically synced from legislative partners.</p>
                </div>
                <div class="flex gap-3">
                    <button id="simulateSyncBtn" onclick="openSimulatorModal()" class="no-ripple inline-flex items-center justify-center bg-white text-red-700 px-5 py-2.5 rounded-xl font-bold hover:bg-red-50 transition-shadow shadow-sm min-w-[160px] h-10 flex-shrink-0 transform-none hover:transform-none active:transform-none">
                        <i class="bi bi-cpu mr-2"></i> Simulate Sync
                    </button>
                </div>
            </div>
        </div>

        <!-- Integration Status Bar -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div class="text-gray-500 text-xs uppercase font-bold tracking-wider mb-1">Status</div>
                <div class="flex items-center text-green-600 font-bold">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                    Connected & Live
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div class="text-gray-500 text-xs uppercase font-bold tracking-wider mb-1">Last Sync</div>
                <div class="text-gray-900 font-semibold">Just now</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div class="text-gray-500 text-xs uppercase font-bold tracking-wider mb-1">Source System</div>
                <div class="text-gray-900 font-semibold">Unified API Gateway</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div class="text-gray-500 text-xs uppercase font-bold tracking-wider mb-1">Records Today</div>
                <div class="text-gray-900 font-semibold"><?php echo count($records); ?></div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Received At</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Source System</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">External Information</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Attachment</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                            No integrated records found for this module yet.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($records as $record): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo date('M d, Y H:i', strtotime($record['received_at'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded uppercase">
                                    <?php echo htmlspecialchars($record['source_system']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900"><?php echo htmlspecialchars($record['title']); ?></div>
                                <div class="text-xs text-gray-500 truncate max-w-xs"><?php echo htmlspecialchars($record['summary']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $payload = json_decode($record['data_payload'] ?? '{}', true) ?: [];
                                $attachName = $payload['file_name'] ?? null;
                                $attachType = $payload['file_type'] ?? null;
                                ?>
                                <?php if ($attachName): ?>
                                <div class="flex items-center gap-2">
                                    <div class="<?php echo getIntFileIconBg($attachType, $attachName); ?> rounded-lg p-1.5 flex-shrink-0">
                                        <i class="<?php echo getIntFileIcon($attachType, $attachName); ?> text-lg"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-semibold text-gray-800 truncate max-w-[140px]" title="<?php echo htmlspecialchars($attachName); ?>">
                                            <?php echo htmlspecialchars($attachName); ?>
                                        </div>
                                        <div class="text-[10px] text-gray-400">
                                            <?php
                                            $fSize = $payload['file_size'] ?? 0;
                                            echo $fSize > 1048576 ? number_format($fSize / 1048576, 1) . ' MB' : number_format($fSize / 1024, 1) . ' KB';
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <span class="text-xs text-gray-400 italic">No file</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $statusClasses = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'synced' => 'bg-green-100 text-green-800',
                                    'processed' => 'bg-blue-100 text-blue-800',
                                    'failed' => 'bg-red-100 text-red-800'
                                ];
                                $cls = $statusClasses[$record['status']] ?? 'bg-gray-100';
                                ?>
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $cls; ?>">
                                    <?php echo e(ucfirst($record['status'])); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if ($record['status'] !== 'synced'): ?>
                                <button onclick="importToLRMS(<?php echo $record['id']; ?>, this)" class="text-red-600 hover:text-red-800 font-bold text-sm flex items-center justify-end w-full group">
                                    <i class="bi bi-download mr-1 transition-transform group-hover:translate-y-0.5"></i>
                                    Import to LRMS
                                </button>
                                <?php else: ?>
                                <span class="text-green-600 font-bold text-sm flex items-center justify-end">
                                    <i class="bi bi-check-all text-lg mr-1"></i> Synced
                                </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Footer -->
    <?php require_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<!-- Simulator Modal -->
<div id="simulatorModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden z-50 items-center justify-center p-4">
    <div class="bg-white/90 dark:bg-gray-800/95 backdrop-blur-md rounded-2xl shadow-2xl max-w-md w-full p-6 animate-fade-in-up border border-white/20 dark:border-gray-700/50">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 flex items-center">
                <i class="bi bi-cpu mr-2 text-red-600"></i> Integration Simulator
            </h3>
            <button id="simulatorCloseBtn" onclick="closeSimulatorModal()" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">This tool simulates an external system sending data to the LRMS via the Integration API.</p>
        
        <form id="simulatorForm" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="module_type" value="<?php echo $currentModule; ?>">
            
            <div class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg border border-gray-200 dark:border-gray-600 mb-4">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-500 dark:text-gray-400 font-bold uppercase">Target Module:</span>
                    <span class="text-red-600 dark:text-red-400 font-bold px-2 py-0.5 bg-red-50 dark:bg-red-900/30 rounded italic">
                        <?php echo $config['title']; ?> (<?php echo $currentModule; ?>)
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="relative z-30">
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Source System</label>
                    <div class="relative custom-select-container">
                        <div id="source-system-trigger" class="w-full border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-sm px-4 py-3 cursor-pointer flex items-center justify-between" style="min-height: 48px;">
                            <span id="source-system-value">Sangguniang Office App</span>
                            <i class="bi bi-chevron-down text-gray-400"></i>
                        </div>
                        <div id="source-system-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-xl z-[100] max-h-64 overflow-y-auto">
                            <div class="p-2 space-y-1">
                                <div class="source-system-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="Sangguniang Office App">Sangguniang Office App</div>
                                <div class="source-system-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="Public Records Portal">Public Records Portal</div>
                                <div class="source-system-option px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors" data-value="External Research DB">External Research DB</div>
                            </div>
                        </div>
                        <input type="hidden" name="source_system" id="source-system-input" value="Sangguniang Office App">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">External Ref #</label>
                    <input type="text" name="external_id" placeholder="EXT-2024-001" class="w-full border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:placeholder-gray-500 rounded-lg text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Title / Subject</label>
                <input type="text" name="title" required placeholder="e.g. Resolution for Green Initiatives" class="w-full border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:placeholder-gray-500 rounded-lg text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Document Date</label>
                    <input type="date" name="document_date" value="<?php echo date('Y-m-d'); ?>" class="w-full border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Tags (Comma separated)</label>
                    <input type="text" name="tags" placeholder="Environment, Budget" class="w-full border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:placeholder-gray-500 rounded-lg text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Brief Description</label>
                <textarea name="summary" rows="3" class="w-full border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:placeholder-gray-500 rounded-lg text-sm" placeholder="Details of the record..."></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Attach Document <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="file" name="document_file" id="simulatorFile" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-700 dark:file:bg-gray-600 dark:file:text-gray-200 hover:file:bg-red-100 cursor-pointer">
                </div>
                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">Accepted: PDF, Word (.doc/.docx), Excel (.xls/.xlsx), PowerPoint (.ppt/.pptx) — Max 10MB</p>
            </div>

            <button type="submit" id="simulateBtn" class="w-full bg-red-700 dark:bg-gray-700 text-white font-bold py-3 rounded-xl hover:bg-red-800 dark:hover:bg-gray-600 transition-all flex items-center justify-center">
                <i class="bi bi-send mr-2"></i> Send to LRMS API
            </button>
        </form>
    </div>
</div>

<script>
// Custom Dropdown Helper Function
function initCustomDropdown(triggerId, dropdownId, valueId, inputId, optionClass, defaultValue) {
    const trigger = document.getElementById(triggerId);
    const dropdown = document.getElementById(dropdownId);
    const valueDisplay = document.getElementById(valueId);
    const hiddenInput = document.getElementById(inputId);
    const options = document.querySelectorAll(optionClass);
    
    if (!trigger || !dropdown || !valueDisplay || !hiddenInput) return;
    
    const selectedValue = hiddenInput.value;
    if (selectedValue) {
        const selectedOption = document.querySelector(`${optionClass}[data-value="${selectedValue}"]`);
        if (selectedOption) {
            valueDisplay.textContent = selectedOption.textContent;
        }
    }
    
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
    });
    
    options.forEach(option => {
        option.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            const text = this.textContent;
            valueDisplay.textContent = text;
            hiddenInput.value = value;
            dropdown.classList.add('hidden');
        });
    });
    
    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initCustomDropdown('source-system-trigger', 'source-system-dropdown', 'source-system-value', 'source-system-input', '.source-system-option', 'Sangguniang Office App');
});

function openSimulatorModal() {
    document.getElementById('simulatorModal').classList.remove('hidden');
    document.getElementById('simulatorModal').classList.add('flex');
}

function closeSimulatorModal() {
    document.getElementById('simulatorModal').classList.add('hidden');
    document.getElementById('simulatorModal').classList.remove('flex');
}

function importToLRMS(id, btn) {
    if (!confirm('Are you sure you want to import this record into the main LRMS database? It will be created as a draft document.')) return;

    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin mr-1"></i> Importing...';

    const formData = new FormData();
    formData.append('id', id);

    fetch('<?php echo INTEGRATION_URL; ?>/api/import.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            btn.innerHTML = '<i class="bi bi-check-circle-fill mr-1"></i> Done!';
            btn.classList.remove('text-red-600');
            btn.classList.add('text-green-600');
            setTimeout(() => {
                window.location.reload();
            }, 800);
        } else {
            alert('Import Error: ' + res.error);
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network Error connecting to LRMS API');
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    });
}

document.getElementById('simulatorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('simulateBtn');
    const originalText = btn.innerHTML;
    
    // Validate file
    const fileInput = document.getElementById('simulatorFile');
    if (!fileInput.files.length) {
        alert('Please attach a document file (PDF, Word, or Excel).');
        return;
    }
    
    const file = fileInput.files[0];
    const maxSize = 10 * 1024 * 1024; // 10MB
    const allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
    const fileExt = file.name.split('.').pop().toLowerCase();
    
    if (file.size > maxSize) {
        alert('File is too large. Maximum size is 10MB.');
        return;
    }
    
    if (!allowedExts.includes(fileExt)) {
        alert('Invalid file type. Please upload a PDF, Word, Excel, or PowerPoint file.');
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin mr-2"></i> Uploading & Syncing...';

    const formData = new FormData(this);

    fetch('<?php echo INTEGRATION_URL; ?>/api/receive.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            btn.innerHTML = '<i class="bi bi-check-circle-fill mr-2"></i> Sync Successful!';
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            alert('API Error: ' + res.error);
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network Error connecting to LRMS API');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});
</script>
