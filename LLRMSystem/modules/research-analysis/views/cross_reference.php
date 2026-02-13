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
            <div class="lg:col-span-3 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden relative">
                <div id="map-container" class="w-full h-full bg-gray-50"></div>
                
                <!-- Zoom Controls -->
                <div class="absolute bottom-6 right-6 flex flex-col gap-2">
                    <button id="zoom-in" class="bg-white w-10 h-10 rounded-lg shadow-md border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition-all">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <button id="zoom-out" class="bg-white w-10 h-10 rounded-lg shadow-md border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition-all">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                    <button id="fit-map" class="bg-white w-10 h-10 rounded-lg shadow-md border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition-all">
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
    
    // Create nodes
    const nodes = new vis.DataSet(rawData.nodes.map(node => ({
        id: node.id,
        label: node.label,
        title: `<b>${node.title}</b><br>${node.type}`,
        color: {
            background: node.type === 'ordinance' ? '#dc2626' : '#2563eb',
            border: node.type === 'ordinance' ? '#991b1b' : '#1e40af',
            highlight: '#000'
        },
        font: { color: '#fff', size: 12, face: 'Inter' },
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
.vis-network { outline: none; }
.vis-tooltip {
    background-color: #fff !important;
    padding: 12px !important;
    border-radius: 8px !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1) !important;
    font-family: 'Inter', sans-serif !important;
    max-width: 250px !important;
}
</style>
