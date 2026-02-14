<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../controllers/ResearchController.php';

$controller = new ResearchController();
$data = $controller->getCrossReferenceData();

$pageTitle = 'Legislative Cross-Reference Map';
$currentPage = 'research-analysis';

require_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php require_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php require_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
        <div class="mb-4">
            <a href="index.php" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                <i class="bi bi-arrow-left mr-2"></i> Back to Analysis Dashboard
            </a>
        </div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Legislative Cross-Reference Map</h1>
            <p class="text-gray-600">Visualizing relationships and dependencies between ordinances and resolutions.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 h-[calc(100vh-250px)]">
            <!-- Sidebar: Stats & Info -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                        <i class="bi bi-info-circle mr-2 text-red-600"></i> Map Statistics
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Total Nodes (Docs)</span>
                            <span class="font-bold text-gray-900"><?= count($data['nodes']) ?></span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Detected Links</span>
                            <span class="font-bold text-gray-900"><?= count($data['links']) ?></span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4">Legend</h3>
                    <div class="space-y-2">
                        <div class="flex items-center text-sm">
                            <span class="w-3 h-3 rounded-full bg-red-600 mr-2"></span>
                            <span>Ordinance</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <span class="w-3 h-3 rounded-full bg-blue-600 mr-2"></span>
                            <span>Resolution</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Map Canvas -->
            <div class="lg:col-span-3 bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden relative">
                <div id="map-container" class="w-full h-full bg-gray-50 dark:bg-gray-800"></div>
                
                <!-- Zoom Controls -->
                <div class="absolute bottom-6 right-6 flex flex-col gap-2">
                    <button id="zoom-in" class="bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 w-10 h-10 rounded-lg shadow-md border border-gray-200 dark:border-gray-600 flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-600 transition-all">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <button id="zoom-out" class="bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 w-10 h-10 rounded-lg shadow-md border border-gray-200 dark:border-gray-600 flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-600 transition-all">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                    <button id="fit-map" class="bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 w-10 h-10 rounded-lg shadow-md border border-gray-200 dark:border-gray-600 flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-600 transition-all">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<!-- Visualization Library -->
<script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rawData = <?= json_encode($data) ?>;
    
    // Function to get label color based on current theme
    function getLabelColor() {
        return document.documentElement.classList.contains('dark') ? '#ffffff' : '#1f2937';
    }
    
    // Create nodes with dynamic font color
    const nodes = new vis.DataSet(rawData.nodes.map(node => ({
        id: node.id,
        label: node.label,
        title: `<b>${node.title}</b><br>${node.type}`,
        color: {
            background: node.type === 'ordinance' ? '#dc2626' : '#2563eb',
            border: node.type === 'ordinance' ? '#991b1b' : '#1e40af',
            highlight: '#000'
        },
        font: { 
            color: getLabelColor(),
            size: 14, 
            face: 'Inter, system-ui, sans-serif',
            bold: true,
            strokeWidth: 3,
            strokeColor: document.documentElement.classList.contains('dark') ? '#1f2937' : '#f9fafb'
        },
        shape: 'dot',
        size: 20
    })));

    // Create edges
    const edges = new vis.DataSet(rawData.links.map(link => ({
        from: link.source,
        to: link.target,
        arrows: 'to',
        color: { color: '#94a3b8', highlight: '#dc2626' },
        width: 1,
        smooth: { type: 'curvedCW' }
    })));

    const container = document.getElementById('map-container');
    const data = { nodes, edges };
    const options = {
        interaction: {
            hover: true,
            tooltipDelay: 200
        },
        physics: {
            enabled: true,
            barnesHut: {
                gravitationalConstant: -2000,
                centralGravity: 0.3,
                springLength: 150
            },
            stabilization: { iterations: 100 }
        }
    };

    const network = new vis.Network(container, data, options);

    // Update node labels when theme changes
    const updateNodeLabels = () => {
        const labelColor = getLabelColor();
        const strokeColor = document.documentElement.classList.contains('dark') ? '#1f2937' : '#f9fafb';
        nodes.forEach(node => {
            nodes.update({
                id: node.id,
                font: {
                    color: labelColor,
                    size: 14,
                    face: 'Inter, system-ui, sans-serif',
                    bold: true,
                    strokeWidth: 3,
                    strokeColor: strokeColor
                }
            });
        });
    };

    // Listen for theme changes
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'class') {
                updateNodeLabels();
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });

    // Zoom Controls
    document.getElementById('zoom-in').onclick = () => network.moveTo({ scale: network.getScale() * 1.2 });
    document.getElementById('zoom-out').onclick = () => network.moveTo({ scale: network.getScale() * 0.8 });
    document.getElementById('fit-map').onclick = () => network.fit();

    // Node click
    network.on("click", function (params) {
        if (params.nodes.length > 0) {
            const nodeId = params.nodes[0];
            // window.location.href = `../../document-management/views/view.php?id=${nodeId}`;
        }
    });
});
</script>

<style>
.vis-network { 
    outline: none; 
    background-color: #f9fafb !important;
}
html.dark .vis-network {
    background-color: #1f2937 !important;
}
.vis-tooltip {
    background-color: #fff !important;
    padding: 12px !important;
    border-radius: 8px !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1) !important;
    font-family: 'Inter', sans-serif !important;
    max-width: 250px !important;
}
html.dark .vis-tooltip {
    background-color: #374151 !important;
    border-color: #4b5563 !important;
    color: #f3f4f6 !important;
}
</style>
