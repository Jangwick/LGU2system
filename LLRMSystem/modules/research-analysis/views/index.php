<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../controllers/ResearchController.php';

$controller = new ResearchController();
$trends = $controller->getTrendsData();

$pageTitle = 'Legislative Research & Analysis';
$currentPage = 'research-analysis';

require_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php require_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php require_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
        <!-- Page Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-2xl shadow-xl p-8 mb-6 text-white transform hover:scale-[1.01] transition-all duration-300 animate-fade-in">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="transform transition-all duration-300">
                    <h1 class="text-2xl font-bold mb-2 animate-slide-in-left text-white">Legislative Research & Analysis</h1>
                    <p class="text-red-100 dark:text-red-200 animate-slide-in-left animation-delay-100">Intelligent trends, comparisons, and topical insights</p>
                </div>
                <div class="flex gap-3 animate-slide-in-right">
                    <a href="compare.php" style="background-color: #ffffff !important; color: #b91c1c !important;" class="!bg-white !text-red-700 hover:!bg-red-50 font-bold py-2 px-6 rounded-lg transition-all duration-200 flex items-center shadow-md">
                        <i class="bi bi-layout-split mr-2"></i> Comparison Tool
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Topic Trends Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 transform hover:shadow-lg transition-all duration-300">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <i class="bi bi-tags-fill text-red-600 mr-2"></i> Top Legislative Topics
                </h3>
                <div class="h-80">
                    <canvas id="topicTrendsChart"></canvas>
                </div>
            </div>

            <!-- Volume Growth Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 transform hover:shadow-lg transition-all duration-300">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <i class="bi bi-graph-up-arrow text-red-600 mr-2"></i> Record Volume Trends
                </h3>
                <div class="h-80">
                    <canvas id="volumeTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Access Tools -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 hover:border-red-300 dark:hover:border-red-500 transition-all group">
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center text-red-600 dark:text-red-400 mb-4 group-hover:scale-110 transition-transform">
                    <i class="bi bi-search text-2xl"></i>
                </div>
                <h4 class="font-bold text-gray-900 dark:text-gray-100 mb-2">Semantic Search</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Find documents using AI-powered meaning rather than just exact keywords.</p>
                <a href="<?php echo SEARCH_URL; ?>/views/index.php" class="text-red-600 dark:text-red-400 font-bold text-sm hover:underline">Explore →</a>
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 hover:border-red-300 dark:hover:border-blue-500 transition-all group">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400 mb-4 group-hover:scale-110 transition-transform">
                    <i class="bi bi-layout-split text-2xl"></i>
                </div>
                <h4 class="font-bold text-gray-900 dark:text-gray-100 mb-2">Law Comparison</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Compare multiple versions or related ordinances side-by-side.</p>
                <a href="compare.php" class="text-blue-600 dark:text-blue-400 font-bold text-sm hover:underline">Compare Now →</a>
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 hover:border-red-300 dark:hover:border-green-500 transition-all group">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center text-green-600 dark:text-green-400 mb-4 group-hover:scale-110 transition-transform">
                    <i class="bi bi-file-earmark-diff text-2xl"></i>
                </div>
                <h4 class="font-bold text-gray-900 dark:text-gray-100 mb-2">Cross-Reference</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Analyze relationships between different resolutions and ordinances.</p>
                <a href="cross_reference.php" class="text-green-600 dark:text-green-400 font-bold text-sm hover:underline">View Map →</a>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/../../core/layouts/footer.php'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Topic Trends Chart
    const topicCtx = document.getElementById('topicTrendsChart').getContext('2d');
    new Chart(topicCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($trends['top_topics'], 'topic')); ?>,
            datasets: [{
                label: 'Documents',
                data: <?php echo json_encode(array_column($trends['top_topics'], 'count')); ?>,
                backgroundColor: 'rgba(220, 38, 38, 0.7)',
                borderColor: 'rgb(220, 38, 38)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Volume History Chart
    const volumeCtx = document.getElementById('volumeTrendChart').getContext('2d');
    new Chart(volumeCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($trends['volume_history'], 'month')); ?>,
            datasets: [{
                label: 'Records Created',
                data: <?php echo json_encode(array_column($trends['volume_history'], 'count')); ?>,
                fill: true,
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                borderColor: 'rgb(220, 38, 38)',
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>
