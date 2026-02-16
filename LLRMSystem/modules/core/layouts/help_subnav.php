<?php
/**
 * Help Pages Sub-Navigation
 * Breadcrumb + quick links between help/legal pages
 * 
 * Usage: Set $currentHelpPage before including this file
 * Available values: 'faq', 'privacy', 'terms', 'contact', 'help'
 */

$currentHelpPage = $currentHelpPage ?? '';

$helpPages = [
    'faq'     => ['label' => 'FAQ',           'icon' => 'bi-patch-question',    'url' => HELP_URL . '/views/faq.php'],
    'contact' => ['label' => 'Support',       'icon' => 'bi-headset',           'url' => HELP_URL . '/views/contact.php'],
    'privacy' => ['label' => 'Privacy',       'icon' => 'bi-shield-lock',       'url' => HELP_URL . '/views/privacy.php'],
    'terms'   => ['label' => 'Terms',         'icon' => 'bi-file-earmark-ruled','url' => HELP_URL . '/views/terms.php'],
];
?>

<!-- Help Sub-Navigation -->
<div class="max-w-4xl mx-auto mb-6">
    <!-- Breadcrumb -->
    <nav class="inline-flex items-baseline text-xs font-bold text-gray-400 mb-4 px-1 overflow-x-auto whitespace-nowrap" aria-label="Breadcrumb">
        <a href="<?php echo BASE_URL; ?>/index.php" class="hover:text-red-600 transition-colors shrink-0">
            <i class="bi bi-house-door mr-1 align-baseline"></i>Home
        </a>
        <i class="bi bi-chevron-right mx-1.5 md:mx-2 text-[10px] shrink-0 align-baseline"></i>
        <?php if ($currentHelpPage && isset($helpPages[$currentHelpPage])): ?>
            <a href="<?php echo HELP_URL; ?>/views/faq.php" class="hover:text-red-600 transition-colors shrink-0">Help &amp; Support</a>
            <i class="bi bi-chevron-right mx-1.5 md:mx-2 text-[10px] shrink-0 align-baseline"></i>
            <span class="text-gray-700 dark:text-gray-200 shrink-0"><?php echo $helpPages[$currentHelpPage]['label']; ?></span>
        <?php else: ?>
            <span class="text-gray-700 dark:text-gray-200 shrink-0">Help &amp; Support</span>
        <?php endif; ?>
    </nav>
    
    <!-- Quick Page Navigation Tabs -->
    <div class="bg-white dark:bg-[#2d2d2d] rounded-xl border border-gray-100 dark:border-[#404040] shadow-sm p-1.5 flex flex-wrap gap-1">
        <?php foreach ($helpPages as $key => $page): ?>
            <a href="<?php echo $page['url']; ?>" 
               class="hero-toggle-btn flex-1 min-w-[80px] flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all duration-200 <?php echo $currentHelpPage === $key ? 'bg-red-600 text-white shadow-md' : 'text-gray-500 dark:text-gray-400 hover:text-red-600 hover:bg-gray-50 dark:hover:bg-[#404040]'; ?>">
                <i class="bi <?php echo $page['icon']; ?> text-sm"></i>
                <span class="hidden sm:inline"><?php echo $page['label']; ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
