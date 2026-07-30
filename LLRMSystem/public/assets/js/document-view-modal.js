// Essential Global Handlers (Redefined here for reliability)
function toggleAdvancedFilters() {
    const panel = document.getElementById('advanced-filters-panel');
    const chevron = document.getElementById('advanced-filters-chevron');
    if (panel) {
        const isHidden = panel.classList.toggle('hidden');
        if (chevron) {
            chevron.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    }
}

function applyFilters() {
    if (window.docManager) window.docManager.applyFilters();
    else if (typeof window.applyFilters === 'function') window.applyFilters();
}

function applyAdvancedFilters() {
    if (window.docManager) window.docManager.applyAdvancedFilters();
}

function clearAdvancedFilters() {
    if (window.docManager) window.docManager.clearFilters();
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function toggleDocPreview(btn) {
    const container = document.getElementById('doc-preview-container');
    const icon = btn.querySelector('i');
    const label = btn.querySelector('span');
    if (container.classList.contains('max-h-72')) {
        container.classList.remove('max-h-72');
        container.classList.add('max-h-[2000px]');
        icon.classList.remove('bi-chevron-down');
        icon.classList.add('bi-chevron-up');
        label.textContent = 'Collapse';
    } else {
        container.classList.remove('max-h-[2000px]');
        container.classList.add('max-h-72');
        icon.classList.remove('bi-chevron-up');
        icon.classList.add('bi-chevron-down');
        label.textContent = 'Expand';
    }
}

function formatDocumentText(rawText) {
    if (!rawText) return '<p class="text-gray-400 italic">No content available.</p>';

    // Normalize line endings
    let text = rawText.replace(/\r\n/g, '\n').replace(/\r/g, '\n');

    // Remove OCR markers
    text = text.replace(/^\[OCR\]\s*\n?/i, '');
    text = text.replace(/\[Page OCR failed:.*?\]/g, '');
    text = text.replace(/--- Page Break ---/g, '\n\n');

    // Split into blocks separated by blank lines
    const blocks = text.split(/\n{2,}/);
    let html = '';
    let inList = false;
    let listType = '';
    let listItems = [];

    const closeList = () => {
        if (inList) {
            const tag = listType === 'ol' ? 'ol' : 'ul';
            const cls = listType === 'ol'
                ? 'list-decimal list-inside space-y-1.5 my-3 pl-2'
                : 'list-disc list-inside space-y-1.5 my-3 pl-2';
            html += `<${tag} class="${cls}">${listItems.join('')}</${tag}>`;
            inList = false;
            listType = '';
            listItems = [];
        }
    };

    for (let block of blocks) {
        block = block.trim();
        if (!block) continue;

        // Check for numbered list items (1., 2., etc.)
        const numberedMatch = block.match(/^(\d+)\.\s*(.+)/);

        // Check for bullet list items (-, *, •)
        const bulletMatch = block.match(/^[•·\-\*]\s*(.+)/);

        // Check for section headers: ROMAN NUMERAL + . or all caps short line
        const romanNumeralMatch = block.match(/^([IVXLCDM]+)\.\s*(.+)/i);
        const isShortAllCaps = block.length < 80 && block === block.toUpperCase() && /[A-Z]/.test(block) && !block.endsWith('.') && !numberedMatch;

        // Check for lettered items (A., B., etc.)
        const letteredMatch = block.match(/^([A-Z])\.\s*(.+)/);

        // Detect headings — lines that look like titles
        const isHeading = isShortAllCaps ||
            (block.length < 100 && block === block.toUpperCase() && /[A-Z]/.test(block)) ||
            /^(DETAILED\s|AN\s|ORDINANCE|RESOLUTION|REPUBLIC\s|CITY\s|MUNICIPAL|PROVINCIAL|BARANGAY|OFFICE\s|DEPARTMENT|COLLEGE|UNIVERSITY|SCHOOL|SECTION|ARTICLE|CHAPTER)/i.test(block) && block.length < 120;

        if (numberedMatch) {
            if (inList && listType !== 'ol') { closeList(); }
            if (!inList) { inList = true; listType = 'ol'; }
            listItems.push(`<li class="text-gray-800 dark:text-gray-200 leading-relaxed">${escapeHtml(numberedMatch[2])}</li>`);
        } else if (bulletMatch) {
            if (inList && listType !== 'ul') { closeList(); }
            if (!inList) { inList = true; listType = 'ul'; }
            listItems.push(`<li class="text-gray-800 dark:text-gray-200 leading-relaxed">${escapeHtml(bulletMatch[1])}</li>`);
        } else if (letteredMatch && block.length < 200) {
            if (inList && listType !== 'ol') { closeList(); }
            if (!inList) { inList = true; listType = 'ol'; }
            listItems.push(`<li class="text-gray-800 dark:text-gray-200 leading-relaxed"><strong>${letteredMatch[1]}.</strong> ${escapeHtml(letteredMatch[2])}</li>`);
        } else {
            closeList();

            if (isHeading) {
                // Major heading — centered, bold, larger
                html += `<h2 class="text-center font-bold text-base text-gray-900 dark:text-gray-100 my-3 uppercase tracking-wide">${escapeHtml(block)}</h2>`;
            } else if (romanNumeralMatch && block.length < 200) {
                // Roman numeral section heading
                html += `<h3 class="font-bold text-sm text-gray-900 dark:text-gray-100 mt-4 mb-2">${escapeHtml(block)}</h3>`;
            } else if (block.length < 100 && /^(Section|Article|Chapter|Title)\s/i.test(block)) {
                // Section heading
                html += `<h3 class="font-bold text-sm text-gray-900 dark:text-gray-100 mt-4 mb-2">${escapeHtml(block)}</h3>`;
            } else {
                // Regular paragraph — handle single line breaks within block
                const lines = block.split('\n');
                if (lines.length === 1) {
                    html += `<p class="text-gray-800 dark:text-gray-200 leading-relaxed mb-3 text-justify">${escapeHtml(block)}</p>`;
                } else {
                    // Multi-line block: check if it's a sub-list or indented content
                    const isIndented = lines.every(l => /^\s+/.test(l) || !l.trim());
                    if (isIndented && lines.length > 2) {
                        html += `<div class="pl-4 border-l-2 border-gray-200 dark:border-gray-700 my-3 space-y-1">`;
                        for (const line of lines) {
                            if (line.trim()) {
                                html += `<p class="text-gray-700 dark:text-gray-300 leading-relaxed text-[12px]">${escapeHtml(line.trim())}</p>`;
                            }
                        }
                        html += `</div>`;
                    } else {
                        // Join lines with <br> for line-by-line content (e.g., addresses)
                        html += `<p class="text-gray-800 dark:text-gray-200 leading-relaxed mb-3 text-justify">${lines.map(l => escapeHtml(l.trim())).join('<br>')}</p>`;
                    }
                }
            }
        }
    }
    closeList();

    return html;
}

function viewDocument(id) {
    const modal = document.getElementById('preview-modal');
    const content = document.getElementById('preview-content');
    const modalContainer = document.getElementById('preview-modal-panel');
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    content.innerHTML = `
        <div class="flex items-center justify-center p-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-600"></div>
        </div>
    `;

    // Animation classes
    setTimeout(() => {
        modalContainer.classList.remove('translate-y-full', 'opacity-0');
        modalContainer.classList.add('translate-y-0', 'opacity-100');
    }, 10);

    fetch(App.apiUrl('documents', `get_details.php?id=${id}`))
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const doc = res.document;
                const statusBadge = getStatusBadgeHTML(doc.status);
                
                content.innerHTML = `
                    <div class="p-4 md:p-8">
                        <!-- Top Header Area -->
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6 mb-8 pb-6 border-b border-gray-100 dark:border-gray-800">
                            <div>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-3">
                                    <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white leading-tight">${doc.title}</h2>
                                    <div class="flex">${statusBadge}</div>
                                </div>
                                <div class="flex flex-wrap items-center gap-y-3 text-xs md:text-sm text-gray-500 dark:text-gray-400">
                                    <span class="flex items-center bg-gray-50 dark:bg-gray-800/50 px-2 py-1 rounded-lg border border-gray-100 dark:border-gray-700">
                                        <i class="bi bi-hash mr-1.5 text-red-500"></i>
                                        REF: <span class="font-black text-gray-800 dark:text-gray-200 ml-1 uppercase">${doc.reference_number}</span>
                                    </span>
                                    <span class="hidden md:inline mx-3 text-gray-300 dark:text-gray-700">|</span>
                                    <span class="flex items-center">
                                        <i class="bi bi-file-earmark-text mr-1.5 text-blue-500"></i>
                                        Type: <span class="capitalize ml-1 font-bold text-gray-700 dark:text-gray-300">${doc.document_type}</span>
                                    </span>
                                    <span class="mx-3 text-gray-300 dark:text-gray-700">|</span>
                                    <span class="flex items-center">
                                        <i class="bi bi-calendar3 mr-1.5 text-green-500"></i>
                                        Date: <span class="ml-1 font-bold text-gray-700 dark:text-gray-300">${formatDate(doc.document_date)}</span>
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-row md:flex-row items-center gap-3">
                                ${currentUserRole !== 'viewer' ? `
                                <a href="${App.apiUrl('documents', `download.php?id=${doc.id}`)}" class="flex-1 sm:flex-none justify-center bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl font-black uppercase tracking-widest text-[11px] flex items-center shadow-lg shadow-red-200 dark:shadow-none transition-all active:scale-95">
                                    <i class="bi bi-download mr-2 text-base"></i> Download
                                </a>
                                ` : ''}
                                ${currentUserRole !== 'viewer' ? `
                                <button type="button" onclick="editDocument(${doc.id})" class="flex-1 sm:flex-none justify-center bg-gray-900 dark:bg-black hover:bg-black text-white px-6 py-3 rounded-xl font-black uppercase tracking-widest text-[11px] flex items-center shadow-lg transition-all active:scale-95">
                                    <i class="bi bi-pencil-square mr-2 text-base"></i> Edit
                                </button>
                                ` : ''}
                            </div>
                        </div>

                        ${currentUserRole === 'viewer' ? `
                        <div class="bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-500 rounded-r-xl p-4 mb-6">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-info-circle-fill text-amber-500 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-black text-amber-800 dark:text-amber-200 uppercase tracking-widest mb-1">View-Only Access</h4>
                                    <p class="text-sm text-amber-700 dark:text-amber-300">
                                        Your account has view-only access. You can view document details but cannot download or edit files. Contact an administrator if you need download permissions.
                                    </p>
                                </div>
                            </div>
                        </div>
                        ` : ''}

                        <!-- Main Content Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Left: Primary Information -->
                            <div class="lg:col-span-2 space-y-8">
                                <section class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 p-5 md:p-6">
                                    <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 mb-6 flex items-center uppercase tracking-widest">
                                        <span class="w-1 h-5 bg-red-600 rounded-full mr-3"></span>
                                        Document Details
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">File Name</label>
                                            <p class="text-sm text-gray-800 dark:text-gray-200 font-bold break-all">${doc.file_name}</p>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">File Size</label>
                                            <p class="text-sm text-gray-800 dark:text-gray-200 font-bold">${formatSize(doc.file_size)}</p>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Category</label>
                                            <p class="text-sm text-gray-800 dark:text-gray-200 font-bold uppercase">${doc.file_type}</p>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Uploaded By</label>
                                            <p class="text-sm text-gray-800 dark:text-gray-200 font-bold">${doc.uploader_name || 'System Admin'}</p>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Registered On</label>
                                            <p class="text-sm text-gray-800 dark:text-gray-200 font-bold">${formatDateTime(doc.created_at)}</p>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Last Interaction</label>
                                            <p class="text-sm text-gray-800 dark:text-gray-200 font-bold">${formatDateTime(doc.updated_at)}</p>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Status Set By</label>
                                            <p class="text-sm text-gray-800 dark:text-gray-200 font-bold">${doc.status_changed_by_name ? `${doc.status_changed_by_name}` : 'N/A'}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-8 pt-6 border-t border-gray-50 dark:border-gray-800">
                                        <label class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2.5">Description / Annotations</label>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed font-medium">${doc.description || 'No additional notes provided for this record.'}</p>
                                    </div>
                                </section>

                                <!-- Document Analysis (OCR) -->
                                ${(() => {
                                    const ocrStatus = doc.ocr_status || 'pending';
                                    const ocrInfo = {
                                        'completed': { badge: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/50', icon: 'patch-check-fill', label: 'Digitally Extracted' },
                                        'pending': { badge: 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400 border-amber-200 dark:border-amber-800/50', icon: 'hourglass-split', label: 'Extraction Scheduled' },
                                        'processing': { badge: 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400 border-blue-200 dark:border-blue-800/50', icon: 'arrow-repeat', label: 'Extracting...' },
                                        'failed': { badge: 'bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:text-rose-400 border-rose-200 dark:border-rose-800/50', icon: 'exclamation-triangle-fill', label: 'Extraction Unavailable' },
                                        'skipped': { badge: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border-slate-200 dark:border-slate-700', icon: 'dash-circle-fill', label: 'Extraction Skipped' }
                                    };
                                    const info = ocrInfo[ocrStatus] || ocrInfo['pending'];
                                    const keyPoints = doc.key_points ? doc.key_points.split('\n').filter(p => p.trim()) : [];
                                    const extractedText = doc.extracted_text || '';
                                    const processedDate = doc.ocr_processed_at ? new Date(doc.ocr_processed_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : null;
                                    const wordCount = extractedText ? extractedText.trim().split(/\s+/).length : 0;

                                    return `
                                    <section class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                                        <!-- Header Bar -->
                                        <div class="px-5 md:px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gradient-to-r from-slate-50 to-white dark:from-gray-800/80 dark:to-gray-800/50">
                                            <div class="flex items-center">
                                                <span class="w-1 h-5 bg-indigo-600 rounded-full mr-3"></span>
                                                <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest">
                                                    Document Analysis
                                                </h3>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider ${info.badge}">
                                                    <i class="bi bi-${info.icon}"></i>${info.label}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="p-5 md:p-6 space-y-6">
                                            ${processedDate ? `
                                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-gray-400 dark:text-gray-500">
                                                    <span class="flex items-center"><i class="bi bi-calendar-check mr-1.5"></i>Extracted on ${processedDate}</span>
                                                    ${extractedText ? `<span class="flex items-center"><i class="bi bi-file-earmark-text mr-1.5"></i>${extractedText.length.toLocaleString()} characters &middot; ${wordCount.toLocaleString()} words</span>` : ''}
                                                </div>
                                            ` : ''}

                                            ${keyPoints.length > 0 ? `
                                                <!-- Summary of Key Points -->
                                                <div>
                                                    <div class="flex items-center mb-3">
                                                        <i class="bi bi-card-text text-indigo-600 dark:text-indigo-400 mr-2"></i>
                                                        <h4 class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">Summary of Key Points</h4>
                                                    </div>
                                                    <ol class="space-y-2.5 border-l-2 border-indigo-100 dark:border-indigo-900/50 pl-5">
                                                        ${keyPoints.map((p, i) => `
                                                            <li class="relative">
                                                                <span class="absolute -left-[27px] top-0 w-5 h-5 rounded-full bg-indigo-600 text-white text-[10px] font-bold flex items-center justify-center">${i + 1}</span>
                                                                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed pt-0.5">${escapeHtml(p.replace(/^[•·\-\*]\s*/, ''))}</p>
                                                            </li>
                                                        `).join('')}
                                                    </ol>
                                                </div>
                                            ` : ''}

                                            ${extractedText ? `
                                                <!-- Document Preview -->
                                                <div>
                                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                                                        <div class="flex items-center min-w-0">
                                                            <i class="bi bi-file-earmark-richtext text-slate-600 dark:text-slate-400 mr-2 flex-shrink-0"></i>
                                                            <h4 class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider truncate">Document Preview</h4>
                                                        </div>
                                                        <div class="grid grid-cols-2 sm:flex items-center gap-2 w-full sm:w-auto">
                                                            <button type="button" data-preview-id="${doc.id}" data-preview-name="${escapeHtml(doc.file_name || '')}" data-preview-type="${escapeHtml(doc.file_type || '')}" data-compliance="${escapeHtml((doc.compliance_status || 'pending'))}" class="btn-original-preview no-ripple min-w-0 w-full sm:w-auto px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 flex items-center justify-center gap-1 whitespace-nowrap" title="Preview">
                                                                <i class="bi bi-eye flex-shrink-0"></i><span>Preview</span>
                                                            </button>
                                                            <button type="button" onclick="toggleDocPreview(this)" class="no-ripple min-w-0 w-full sm:w-auto px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 flex items-center justify-center gap-1 whitespace-nowrap" title="Expand">
                                                                <i class="bi bi-chevron-down flex-shrink-0"></i><span>Expand</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div id="doc-preview-container" class="max-h-72 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-700 transition-all duration-300">
                                                        <div class="bg-white dark:bg-gray-900 p-6 md:p-10 doc-preview-page">
                                                            <div class="doc-content text-[13px] text-gray-800 dark:text-gray-200 leading-7">${formatDocumentText(extractedText)}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ` : !keyPoints.length ? `
                                                <div class="text-center py-10 bg-gray-50 dark:bg-gray-800/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                                    <i class="bi bi-file-earmark-x text-3xl text-gray-300 dark:text-gray-600 mb-3 block"></i>
                                                    <p class="text-sm text-gray-400 dark:text-gray-500 font-medium">${ocrStatus === 'pending' ? 'Document content extraction is scheduled and will be available once processing is complete.' : ocrStatus === 'failed' ? 'Content extraction was unsuccessful. The file may be corrupted or in an unsupported format.' : 'No readable text content was found in this document.'}</p>
                                                </div>
                                            ` : ''}
                                        </div>
                                    </section>
                                    `;
                                })()}

                                <section id="preview-compliance-section" class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 p-6">
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center">
                                        <i class="bi bi-shield-check mr-2 text-emerald-500"></i>
                                        Compliance
                                    </h3>
                                    <div id="preview-compliance-badge" class="mb-3"></div>
                                    <div id="preview-compliance-content"></div>
                                    ${currentUserRole !== 'viewer' ? `
                                    <button type="button" onclick="runComplianceCheckInPreview(${doc.id})" id="preview-run-compliance-btn" class="mt-4 w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg transition">
                                        <i class="bi bi-arrow-repeat mr-1"></i> Run Compliance Check
                                    </button>
                                    ` : ''}
                                </section>

                                <section class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 p-5 md:p-6">
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-6 flex items-center">
                                        <span class="w-1.5 h-6 bg-blue-600 rounded-full mr-3"></span>
                                        Version History
                                    </h3>
                                    ${res.versions && res.versions.length > 0 ? `
                                        <div class="space-y-3">
                                            ${res.versions.map(v => `
                                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/30 rounded-xl border border-gray-100 dark:border-gray-700">
                                                    <div class="flex items-center">
                                                        <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center mr-4">
                                                            <span class="text-blue-600 font-black text-sm">V${v.version_number}</span>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-bold text-gray-800 dark:text-gray-200">${v.file_name}</p>
                                                            <p class="text-[10px] text-gray-500">${formatDateTime(v.created_at)} • ${v.created_by_name}</p>
                                                        </div>
                                                    </div>
                                                    <button type="button" onclick="revertToVersion(${doc.id}, ${v.version_number})" class="text-xs font-black uppercase text-blue-600 hover:text-blue-700">Revert</button>
                                                </div>
                                            `).join('')}
                                        </div>
                                    ` : `
                                        <div class="text-center py-10 bg-gray-50 dark:bg-gray-800/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                            <p class="text-gray-400 italic text-sm">No previous versions available.</p>
                                        </div>
                                    `}
                                </section>
                            </div>

                            <!-- Right: Sidebar Information -->
                            <div class="space-y-6">
                                <section class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 p-6">
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center">
                                        <i class="bi bi-link-45deg mr-2 text-indigo-600"></i>
                                        Related Documents
                                    </h3>
                                    ${res.related && res.related.length > 0 ? `
                                        <div class="space-y-3">
                                            ${res.related.map(r => `
                                                <a href="javascript:void(0)" onclick="viewDocument(${r.id})" class="block p-3 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl transition-colors border border-transparent hover:border-gray-100 dark:hover:border-gray-700">
                                                    <p class="text-xs font-black text-indigo-600 uppercase mb-1">${r.document_type}</p>
                                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200 leading-tight">${r.title}</p>
                                                    <p class="text-[10px] text-gray-500 mt-1">${r.reference_number}</p>
                                                </a>
                                            `).join('')}
                                        </div>
                                    ` : `
                                        <div class="text-center py-6">
                                            <p class="text-gray-400 italic text-sm">No related documents</p>
                                        </div>
                                    `}
                                </section>

                                <section class="bg-white dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800 p-6 shadow-sm">
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-5 flex items-center">
                                        <i class="bi bi-lightning-charge mr-2 text-yellow-500"></i>
                                        Quick Actions
                                    </h3>
                                    <div class="grid gap-3">
                                        <button type="button" onclick="shareDocument(${doc.id})" class="flex items-center w-full px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 transition-colors">
                                            <i class="bi bi-share mr-3 text-blue-500"></i> Share Document
                                        </button>
                                        <button type="button" onclick="window.print()" class="flex items-center w-full px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 transition-colors">
                                            <i class="bi bi-printer mr-3 text-gray-500"></i> Print Details
                                        </button>
                                        <button type="button" onclick="viewActivityHistory(${doc.id})" class="flex items-center w-full px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 transition-colors">
                                            <i class="bi bi-clock-history mr-3 text-purple-500"></i> Activity History
                                        </button>
                                        <div class="mt-2 pt-2 border-t border-gray-50 dark:border-gray-800">
                                            ${doc.status !== 'approved' ? `
                                            <button type="button" onclick="deleteDocument(${doc.id})" class="flex items-center w-full px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors">
                                                <i class="bi bi-trash mr-3"></i> Delete Document
                                            </button>
                                            ` : ''}
                                        </div>
                                    </div>
                                </section>

                            </div>
                        </div>
                    </div>
                `;
                loadComplianceForPreview(doc.id);
            } else {
                content.innerHTML = `<div class="p-12 text-center text-red-600">${res.error}</div>`;
            }
        })
        .catch(e => {
            content.innerHTML = `<div class="p-12 text-center text-red-600">Failed to load document details</div>`;
        });
}

function getComplianceBadgeHTML(st) {
    const s = (st ? st.toString().toLowerCase().trim() : '');
    if (!s) return '<span class="badge badge-warning compliance-badge text-[10px]" title="Compliance Pending"><i class="bi bi-hourglass-split mr-0.5"></i>Compliance</span>';
    const b = {
        'pending': '<span class="badge badge-warning compliance-badge text-[10px]" title="Compliance Pending"><i class="bi bi-hourglass-split mr-0.5"></i>Compliance</span>',
        'compliant': '<span class="badge badge-success compliance-badge text-[10px]" title="Compliant"><i class="bi bi-shield-check mr-0.5"></i>Compliance</span>',
        'non_compliant': '<span class="badge badge-danger compliance-badge text-[10px]" title="Non-Compliant"><i class="bi bi-shield-exclamation mr-0.5"></i>Non-Compliant</span>'
    };
    return b[s] || '<span class="badge badge-info compliance-badge text-[10px]"><i class="bi bi-shield mr-0.5"></i>' + s + '</span>';
}

function loadComplianceForPreview(docId, badgeId, contentId, showRunButton) {
    const _badgeId = badgeId || 'preview-compliance-badge';
    const _contentId = contentId || 'preview-compliance-content';
    const badgeEl = document.getElementById(_badgeId);
    const contentEl = document.getElementById(_contentId);
    if (!badgeEl || !contentEl) return;
    contentEl.innerHTML = '<div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"><i class="bi bi-arrow-repeat animate-spin"></i>Loading compliance analysis...</div>';
    fetch(App.apiUrl('documents', 'get-compliance-results.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': App.getCsrfToken() },
        body: JSON.stringify({ document_id: docId })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            badgeEl.innerHTML = getComplianceBadgeHTML(data.compliance_status || 'pending');
            let html = '<div class="space-y-3">';
            if (data.rejection_notes) {
                html += '<div class="p-3 rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-900/40 text-red-800 dark:text-red-300 text-sm">' +
                    '<p class="font-semibold mb-1"><i class="bi bi-exclamation-circle mr-1"></i>Rejection Notes</p>' +
                    '<p>' + escapeHtml(data.rejection_notes) + '</p>' +
                '</div>';
            }
            if (data.results && data.results.length > 0) {
                html += '<div class="space-y-3">';
                data.results.forEach(r => {
                    const compliant = r.status === 'compliant';
                    const ai = r.ai_analysis ? (function() { try { return JSON.parse(r.ai_analysis); } catch (e) { return null; } })() : null;
                    html += '<div class="p-4 rounded-xl border ' + (compliant ? 'border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-900/40' : 'border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-900/40') + '">' +
                        '<div class="flex flex-wrap items-start justify-between gap-2 mb-2">' +
                            '<div>' +
                                '<p class="text-sm font-bold text-gray-800 dark:text-gray-200">' + escapeHtml(r.title || 'Unknown standard') + '</p>' +
                                '<p class="text-[10px] text-gray-500 dark:text-gray-400">' + escapeHtml(r.code || '') + (r.is_mandatory ? ' · Mandatory' : '') + (r.weight ? ' · Weight ' + r.weight : '') + '</p>' +
                            '</div>' +
                            '<span class="text-[10px] font-bold uppercase px-2 py-1 rounded-md ' + (compliant ? 'bg-green-200 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-200 text-red-800 dark:bg-red-900/40 dark:text-red-300') + '">' + (compliant ? 'Compliant' : 'Non-Compliant') + '</span>' +
                        '</div>' +
                        (r.summary ? '<p class="text-xs text-gray-600 dark:text-gray-400 mb-3">' + escapeHtml(r.summary) + '</p>' : '') +
                        '<div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs mb-3">' +
                            '<div class="p-2 rounded-lg bg-white/60 dark:bg-gray-800/40">' +
                                '<p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase">Local Score</p>' +
                                '<p class="font-black ' + (parseFloat(r.score) >= 70 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300') + '">' + parseFloat(r.score || 0).toFixed(1) + '%</p>' +
                            '</div>' +
                            '<div class="p-2 rounded-lg bg-white/60 dark:bg-gray-800/40">' +
                                '<p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase">Decision Source</p>' +
                                '<p class="font-black text-gray-700 dark:text-gray-300">' + (ai ? 'AI + Local' : 'Local Only') + '</p>' +
                            '</div>' +
                        '</div>' +
                        (r.matched_keywords ? '<p class="text-xs text-gray-600 dark:text-gray-400 mb-2"><span class="font-bold">Matched:</span> ' + escapeHtml(r.matched_keywords) + '</p>' : '') +
                        (r.explanation ? '<p class="text-xs text-gray-600 dark:text-gray-400 mb-2"><span class="font-bold">Local Analysis:</span> ' + escapeHtml(r.explanation) + '</p>' : '') +
                        (ai ? '<div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700/50">' +
                            '<p class="text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase mb-2"><i class="bi bi-robot mr-1"></i>AI Analysis</p>' +
                            (ai.confidence !== undefined ? '<p class="text-xs text-gray-700 dark:text-gray-300 mb-1"><span class="font-bold">Confidence:</span> ' + parseFloat(ai.confidence).toFixed(1) + '%</span></p>' : '') +
                            (ai.status ? '<p class="text-xs text-gray-700 dark:text-gray-300 mb-1"><span class="font-bold">AI Verdict:</span> ' + escapeHtml(ai.status) + '</p>' : '') +
                            (ai.reason ? '<p class="text-xs text-gray-600 dark:text-gray-400"><span class="font-bold">Reason:</span> ' + escapeHtml(ai.reason) + '</p>' : '') +
                            (ai.evidence ? '<p class="text-xs text-gray-600 dark:text-gray-400 mt-1"><span class="font-bold">Evidence:</span> ' + escapeHtml(ai.evidence) + '</p>' : '') +
                        '</div>' : '') +
                    '</div>';
                });
                html += '</div>';
            } else {
                html += '<p class="text-sm text-gray-500 dark:text-gray-400">No compliance analysis available yet.</p>';
                if (showRunButton && currentUserRole !== 'viewer') {
                    html += '<button type="button" onclick="runComplianceCheckInPreview(' + docId + ', \'' + _badgeId + '\', \'' + _contentId + '\')" class="mt-4 w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-lg transition flex items-center justify-center gap-2">' +
                        '<i class="bi bi-shield-check"></i> Run Compliance Check' +
                    '</button>';
                }
            }
            html += '</div>';
            const needsReview = (data.compliance_status === 'non_compliant' || data.compliance_status === 'needs_review') && data.results && data.results.length > 0;
            if (needsReview && currentUserRole !== 'viewer') {
                html += '<div class="mt-6 p-4 rounded-xl border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-900/40">' +
                    '<p class="text-sm font-bold text-red-800 dark:text-red-200 mb-2"><i class="bi bi-pencil-square mr-1"></i>Revision Notes</p>' +
                    '<p class="text-xs text-red-700 dark:text-red-300 mb-2">Describe what is wrong or needs correction for this document/ordinance.</p>' +
                    '<textarea id="preview-revision-comment" rows="3" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500 focus:border-transparent mb-3" placeholder="Enter revision comment..."></textarea>' +
                    '<button type="button" onclick="submitRejectionComment(' + docId + ')" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-lg transition">' +
                        '<i class="bi bi-send mr-1"></i> Submit Revision Comment' +
                    '</button>' +
                '</div>';
            }
            contentEl.innerHTML = html;
        } else {
            contentEl.innerHTML = '<p class="text-sm text-red-600">Could not load compliance analysis: ' + escapeHtml(data.error || 'Unknown error') + '</p>';
        }
    }).catch(() => {
        contentEl.innerHTML = '<p class="text-sm text-red-600">Could not load compliance analysis.</p>';
    });
}

async function runComplianceCheckInPreview(docId, badgeId, contentId) {
    const _badgeId = badgeId || 'preview-compliance-badge';
    const _contentId = contentId || 'preview-compliance-content';
    const contentEl = document.getElementById(_contentId);
    const badgeEl = document.getElementById(_badgeId);
    if (contentEl) contentEl.innerHTML = '<div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"><i class="bi bi-arrow-repeat animate-spin"></i>Running compliance check...</div>';
    try {
        const response = await fetch(App.apiUrl('documents', 'check-compliance.php'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': App.getCsrfToken() },
            body: JSON.stringify({ document_id: docId })
        });
        const data = await response.json();
        if (data.success) {
            const badgeHTML = getComplianceBadgeHTML(data.compliance_status || 'pending');
            if (badgeEl) badgeEl.innerHTML = badgeHTML;
            loadComplianceForPreview(docId, _badgeId, _contentId);
            // Keep the main document preview modal in sync if it is open behind the iframe
            if (document.getElementById('preview-compliance-badge') && document.getElementById('preview-compliance-content')) {
                loadComplianceForPreview(docId);
            }
            // Update the same document's badge in the document list without a full reload
            const listBadge = document.querySelector('tr[data-document-id="' + docId + '"] .compliance-badge');
            if (listBadge) listBadge.outerHTML = badgeHTML;
        } else {
            if (contentEl) contentEl.innerHTML = '<p class="text-sm text-red-600">Compliance check failed: ' + escapeHtml(data.error || 'Unknown error') + '</p>';
        }
    } catch (e) {
        if (contentEl) contentEl.innerHTML = '<p class="text-sm text-red-600">Failed to run compliance check.</p>';
    }
}

async function submitRejectionComment(docId) {
    const textarea = document.getElementById('preview-revision-comment');
    const contentEl = document.getElementById('preview-compliance-content');
    if (!textarea || !contentEl) return;
    const comment = textarea.value.trim();
    if (!comment) {
        alert('Please enter a revision comment.');
        return;
    }
    try {
        const response = await fetch(App.apiUrl('documents', 'reject-document.php'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': App.getCsrfToken() },
            body: JSON.stringify({ document_id: docId, comment: comment })
        });
        const data = await response.json();
        if (data.success) {
            showToast('Revision comment submitted.', 'success');
            loadComplianceForPreview(docId);
        } else {
            alert(data.error || 'Failed to submit revision comment.');
        }
    } catch (e) {
        alert('Failed to submit revision comment.');
    }
}

/**
 * Handle document sharing
 */
function shareDocument(id) {
    const url = window.location.origin + App.apiUrl('documents', `download.php?id=${id}`);
    
    if (navigator.share) {
        navigator.share({
            title: 'Share Document',
            url: url
        }).catch(err => console.error('Error sharing:', err));
    } else {
        // Fallback: Copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            alert('Document link copied to clipboard!');
        }).catch(err => {
            console.error('Failed to copy link:', err);
        });
    }
}

/**
 * View document activity history
 */
async function viewActivityHistory(id) {
    const modal = document.getElementById('activity-modal');
    const content = document.getElementById('activity-content');
    const modalContent = document.getElementById('activity-modal-content');
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
        if (modalContent) {
            modalContent.classList.remove('translate-y-full', 'sm:scale-95', 'opacity-0');
            modalContent.classList.add('translate-y-0', 'sm:scale-100', 'opacity-100');
        }
    }, 10);
    content.innerHTML = '<div class="p-8 text-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600 mx-auto"></div></div>';
    
    try {
        const response = await fetch(App.apiUrl('documents', `get_details.php?id=${id}`));
        const res = await response.json();
        
        if (res.success && res.activity) {
            if (res.activity.length === 0) {
                content.innerHTML = '<div class="p-8 text-center text-gray-500 italic">No activity recorded for this document.</div>';
            } else {
                content.innerHTML = `
                    <div class="px-6 py-4">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                ${res.activity.map((a, idx) => `
                                    <li>
                                        <div class="relative pb-8">
                                            ${idx !== res.activity.length - 1 ? '<span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-800"></span>' : ''}
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-900 ${
                                                        a.action.includes('upload') || a.action.includes('create') ? 'bg-green-500' :
                                                        a.action.includes('delete') ? 'bg-red-500' :
                                                        a.action.includes('update') ? 'bg-blue-500' :
                                                        a.action.includes('download') ? 'bg-indigo-500' : 'bg-gray-400'
                                                    }">
                                                        <i class="bi ${
                                                            a.action.includes('upload') || a.action.includes('create') ? 'bi-cloud-upload' :
                                                            a.action.includes('delete') ? 'bi-trash' :
                                                            a.action.includes('update') ? 'bi-pencil' :
                                                            a.action.includes('download') ? 'bi-download' : 'bi-eye'
                                                        } text-white text-xs"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-1 min-w-0 pt-1.5">
                                                    <div class="flex flex-col">
                                                        <p class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-tight">${a.action.replace(/_/g, ' ')}</p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">${a.description || 'Action performed on document'}</p>
                                                    </div>
                                                    <div class="mt-2 flex items-center gap-2">
                                                        <span class="text-[10px] font-black text-gray-400 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">${a.user_name}</span>
                                                        <span class="text-[10px] text-gray-400">${formatDateTime(a.created_at)}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                `).join('')}
                            </ul>
                        </div>
                    </div>
                `;
            }
        } else {
            content.innerHTML = `<div class="p-8 text-center text-red-600">${res.error || 'Failed to load activity'}</div>`;
        }
    } catch (e) {
        content.innerHTML = '<div class="p-8 text-center text-red-600">Failed to load activity logs</div>';
    }
}

function closeActivityModal() {
    const modal = document.getElementById('activity-modal');
    const modalContent = document.getElementById('activity-modal-content');
    if (modalContent) {
        modalContent.classList.add('translate-y-full', 'sm:scale-95', 'opacity-0');
        modalContent.classList.remove('translate-y-0', 'sm:scale-100', 'opacity-100');
    }
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }, 300);
}

/**
 * Revert to a specific version
 */
async function revertToVersion(docId, version) {
    if (!confirm(`Are you sure you want to revert to Version ${version}? This will create a new version of the current file.`)) return;
    
    try {
        const formData = new FormData();
        formData.append('document_id', docId);
        formData.append('version_number', version);
        formData.append('csrf_token', App.getCsrfToken());

        const response = await fetch(App.apiUrl('documents', 'revert-version.php'), {
            method: 'POST',
            body: formData
        });
        
        const res = await response.json();
        if (res.success) {
            alert(res.message);
            viewDocument(docId); // Refresh the preview
        } else {
            alert(res.error || 'Failed to revert version');
        }
    } catch (e) {
        alert('Failed to process revert request');
    }
}


function closePreviewModal() {
    const modal = document.getElementById('preview-modal');
    const modalContainer = document.getElementById('preview-modal-panel');
    
    modalContainer.classList.add('translate-y-full', 'opacity-0');
    modalContainer.classList.remove('translate-y-0', 'opacity-100');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }, 300);
}

// Helper functions for modal
function getStatusBadgeHTML(status) {
    if (!status) return '';
    const s = status.toLowerCase();
    const badges = {
        'draft': '<span class="badge badge-secondary"><i class="bi bi-pencil mr-1"></i>Draft</span>',
        'pending': '<span class="badge badge-warning"><i class="bi bi-clock mr-1"></i>Pending</span>',
        'approved': '<span class="badge badge-success"><i class="bi bi-check-circle mr-1"></i>Approved</span>',
        'rejected': '<span class="badge badge-danger"><i class="bi bi-x-circle mr-1"></i>Rejected</span>',
        'archived': '<span class="badge bg-gray-500 text-white"><i class="bi bi-archive mr-1"></i>Archived</span>'
    };
    return badges[s] || `<span class="badge badge-info">${status}</span>`;
}

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

function formatDateTime(dateStr) {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', { 
        year: 'numeric', month: 'long', day: 'numeric', 
        hour: '2-digit', minute: '2-digit' 
    });
}

function formatSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function editDocument(id) {
    // Close the preview modal first if it's open
    const previewModal = document.getElementById('preview-modal');
    if (previewModal && !previewModal.classList.contains('hidden')) {
        closePreviewModal();
    }
    
    const modal = document.getElementById('edit-modal');
    const form = document.getElementById('edit-form-modal');
    const modalContainer = document.getElementById('edit-modal-panel');
    
    // Show modal and start transition
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    setTimeout(() => {
        modalContainer.classList.remove('translate-y-full', 'opacity-0');
        modalContainer.classList.add('translate-y-0', 'opacity-100');
    }, 10);

    // Fetch details to populate form
    fetch(App.apiUrl('documents', `get_details.php?id=${id}`))
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const doc = res.document;
                // Populate fields
                form.querySelector('[name="document_id"]').value = doc.id;
                form.querySelector('[name="title"]').value = doc.title;
                form.querySelector('[name="document_type"]').value = doc.document_type;
                form.querySelector('[name="reference_number"]').value = doc.reference_number;
                form.querySelector('[name="description"]').value = doc.description || '';
                form.querySelector('[name="document_date"]').value = doc.document_date;
                form.querySelector('[name="status"]').value = doc.status;
                form.querySelector('[name="tags"]').value = doc.tags || '';
            } else {
                alert('Error loading document: ' + res.error);
                closeEditModal();
            }
        })
        .catch(err => {
            alert('Failed to connect to API');
            closeEditModal();
        });
}

function closeEditModal() {
    const modal = document.getElementById('edit-modal');
    const modalContainer = document.getElementById('edit-modal-panel');
    
    modalContainer.classList.add('translate-y-full', 'opacity-0');
    modalContainer.classList.remove('translate-y-0', 'opacity-100');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.getElementById('edit-form-modal').reset();
    }, 300);
}

function deleteDocument(id) {
    console.log('deleteDocument called with id:', id);
    if (!confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
        return;
    }
    
    // Close the preview modal if open (so user sees the result)
    const previewModal = document.getElementById('preview-modal');
    if (previewModal && !previewModal.classList.contains('hidden')) {
        closePreviewModal();
    }
    
    fetch(App.apiUrl('documents', 'delete.php'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => {
        console.log('Delete response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Delete response data:', data);
        if (data.success) {
            showToast('Document deleted successfully', 'success');
            closePreviewModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to delete document', 'error');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showToast('An error occurred while deleting', 'error');
    });
}

function showNotification(message, type) {
    // Simple notification (you can enhance this)
    alert(message);
}

function toggleSelectAll(checkbox) {
    if (window.docManager) {
        window.docManager.selectAll(checkbox.checked);
    }

    const documentCheckboxes = document.querySelectorAll('.document-checkbox');
    const selectAllTop = document.getElementById('select-all-top');
    const selectAllHeader = document.getElementById('select-all-header');
    
    documentCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    
    // Sync both select-all checkboxes
    if (selectAllTop) selectAllTop.checked = checkbox.checked;
    if (selectAllHeader) selectAllHeader.checked = checkbox.checked;
    
    updateSelectedCount();
}

function updateSelectedCount() {
    const selected = document.querySelectorAll('.document-checkbox:checked').length;
    const total = document.querySelectorAll('.document-checkbox').length;
    const countElement = document.getElementById('selected-count');
    
    if (selected > 0) {
        countElement.innerHTML = `<span class="font-semibold text-red-600">${selected} selected</span> of ${total} documents`;
    } else {
        countElement.innerHTML = `${total} documents found`;
    }
}

function getSelectedDocumentIds() {
    const checkboxes = document.querySelectorAll('.document-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function exportSelectedFiles() {
    const selectedIds = getSelectedDocumentIds();
    
    if (selectedIds.length === 0) {
        alert('Please select at least one document to export');
        return;
    }
    
    // Close the dropdown
    document.getElementById('export-menu').classList.add('hidden');
    
    // Create export URL with selected IDs
    const url = App.apiUrl('documents', `export.php?export_type=files&ids=${selectedIds.join(',')}`);
    window.location.href = url;
}

function exportAllFiles() {
    if (!confirm('This will download all visible documents as a ZIP file. Continue?')) {
        return;
    }
    
    // Close the dropdown
    document.getElementById('export-menu').classList.add('hidden');
    
    // Get current filters from URL or form
    const searchParams = new URLSearchParams(window.location.search);
    searchParams.set('export_type', 'files');
    const url = App.apiUrl('documents', `export.php?${searchParams.toString()}`);
    window.location.href = url;
}

function exportList(format) {
    // Close the dropdown
    document.getElementById('export-menu').classList.add('hidden');
    
    // Get current filters from URL or form
    const searchParams = new URLSearchParams(window.location.search);
    searchParams.set('export_type', 'list');
    searchParams.set('format', format);
    const url = App.apiUrl('documents', `export.php?${searchParams.toString()}`);
    window.location.href = url;
}

function toggleExportMenu(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    const menu = document.getElementById('export-menu');
    const button = document.querySelector('#export-dropdown button');
    const rect = button.getBoundingClientRect();

    // Move menu to body if not already there (to escape overflow:hidden containers)
    if (menu.parentElement.id === 'export-dropdown') {
        document.body.appendChild(menu);
    }

    // Position the fixed dropdown below the button, aligned to the right
    menu.style.top = (rect.bottom + 8) + 'px';
    menu.style.left = (rect.right - 224) + 'px'; // 224 is the menu width

    menu.classList.toggle('hidden');
}

// Close export menu when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('export-dropdown');
    const menu = document.getElementById('export-menu');
    
    if (dropdown && menu && !dropdown.contains(event.target) && !menu.contains(event.target)) {
        menu.classList.add('hidden');
    }
});

// Update selected count when individual checkboxes change
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.document-checkbox');
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
    
    // Initialize count
    const totalDocs = checkboxes.length;
    if (document.getElementById('total-docs')) {
        document.getElementById('total-docs').textContent = totalDocs;
    }

    // Auto-open upload modal if requested in URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('upload') === 'true') {
        openUploadModal();
        // Remove the parameter from URL without reloading
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }
});

// Upload Modal Functions
function openUploadModal() {
    const modal = document.getElementById('upload-modal');
    const modalContainer = document.getElementById('upload-modal-panel');
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    setTimeout(() => {
        modalContainer.classList.remove('translate-y-full', 'opacity-0');
        modalContainer.classList.add('translate-y-0', 'opacity-100');
    }, 10);
}

function closeUploadModal() {
    const modal = document.getElementById('upload-modal');
    const modalContainer = document.getElementById('upload-modal-panel');
    
    modalContainer.classList.add('translate-y-full', 'opacity-0');
    modalContainer.classList.remove('translate-y-0', 'opacity-100');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        // Reset form
        document.getElementById('upload-form-modal').reset();
        document.getElementById('file-preview-modal').classList.add('hidden');
        document.getElementById('drop-zone-modal').classList.remove('hidden');
    }, 300);
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUploadModal();
        closePreviewModal();
        closeEditModal();
    }
});