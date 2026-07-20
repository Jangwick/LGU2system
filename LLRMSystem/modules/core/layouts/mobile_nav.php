<?php
/**
 * Reusable mobile navigation overlay
 */
?>
<!-- Mobile Nav Overlay -->
<div id="mobile-nav-menu" class="hidden fixed inset-0 z-[200] bg-white overflow-y-auto animate-fade-in-up sm:hidden">
    <div class="fixed top-0 w-full p-4 flex justify-between items-center border-b border-gray-100 bg-white/80 backdrop-blur-md z-10">
        <div class="flex items-center">
            <img src="<?= BASE_URL ?>/public/assets/images/logo.png" alt="Logo" class="h-8 w-8 mr-2 rounded-full shadow-sm" onerror="this.src='<?= BASE_URL ?>/public/assets/images/valenzuela-logo.webp'">
            <span class="text-xl font-black text-[#002d72] tracking-tighter">VALENZUELA<span class="text-red-600">LRMS</span></span>
        </div>
        <button id="mobile-nav-close" class="p-2 text-gray-500 hover:text-red-600 transition-colors">
            <i class="bi bi-x-lg text-2xl"></i>
        </button>
    </div>
    <div class="pt-24 pb-12 px-8 flex flex-col items-center space-y-8 text-center animate-fade-in-up">
        <h3 class="text-gray-900 font-black uppercase tracking-widest text-[10px] mb-2 opacity-50">Quick Links</h3>
        <div class="h-px w-8 bg-red-600/20"></div>
        <a href="<?= PUBLIC_PORTAL_URL ?>" class="mobile-nav-link text-3xl font-black text-red-600 uppercase tracking-tighter hover:text-red-700 transition-colors flex items-center gap-3"><i class="bi bi-globe2"></i>Public Portal</a>
        <div class="h-px w-16 bg-gray-100 my-0"></div>
        <a href="<?= BASE_URL ?>#leadership" class="mobile-nav-link text-3xl font-black text-slate-800 uppercase tracking-tighter hover:text-red-600 transition-colors">Officials</a>
        <a href="<?= BASE_URL ?>#roots" class="mobile-nav-link text-3xl font-black text-slate-800 uppercase tracking-tighter hover:text-red-600 transition-colors">Our History</a>
        <a href="<?= BASE_URL ?>#governance" class="mobile-nav-link text-3xl font-black text-slate-800 uppercase tracking-tighter hover:text-red-600 transition-colors">Governance</a>
        <a href="<?= BASE_URL ?>#recognition" class="mobile-nav-link text-3xl font-black text-slate-800 uppercase tracking-tighter hover:text-red-600 transition-colors">Awards</a>
        <a href="<?= BASE_URL ?>#infrastructure" class="mobile-nav-link text-3xl font-black text-slate-800 uppercase tracking-tighter hover:text-red-600 transition-colors">Infrastructure</a>
        <a href="<?= BASE_URL ?>#landmarks" class="mobile-nav-link text-3xl font-black text-slate-800 uppercase tracking-tighter hover:text-red-600 transition-colors">Landmarks</a>
        <a href="<?= BASE_URL ?>#updates" class="mobile-nav-link text-3xl font-black text-slate-800 uppercase tracking-tighter hover:text-red-600 transition-colors">News & Updates</a>
        <div class="h-px w-16 bg-gray-100 my-2"></div>
        <h3 class="text-gray-400 font-black uppercase tracking-widest text-[10px] mb-0">Help & Legal</h3>
        <a href="<?= HELP_URL ?>/views/privacy.php" class="mobile-nav-link text-lg font-bold text-gray-400 uppercase tracking-wider hover:text-red-600 transition-colors">Privacy</a>
        <a href="<?= HELP_URL ?>/views/terms.php" class="mobile-nav-link text-lg font-bold text-gray-400 uppercase tracking-wider hover:text-red-600 transition-colors">Terms</a>
        <div class="pt-8 w-full border-t border-gray-50 dark:border-gray-800 grid grid-cols-2 gap-4">
            <a href="<?= LOGIN_URL ?>" class="mobile-nav-link flex items-center justify-center border-2 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:text-red-600 hover:border-red-300 dark:hover:border-red-500 font-black uppercase tracking-widest text-sm py-4 rounded-xl transition-all">Sign In</a>
            <a href="<?= REGISTER_URL ?>" class="mobile-nav-link flex items-center justify-center bg-red-600 hover:bg-red-700 text-white font-black uppercase tracking-widest text-sm py-4 rounded-xl shadow-xl shadow-red-200/50">Get Started</a>
        </div>
    </div>
</div>

<script>
    (function(){
        const toggle = document.getElementById('mobile-nav-toggle');
        const close = document.getElementById('mobile-nav-close');
        const menu = document.getElementById('mobile-nav-menu');
        const links = document.querySelectorAll('.mobile-nav-link');
        if(toggle) toggle.addEventListener('click', function(){ menu.classList.remove('hidden'); document.body.style.overflow='hidden'; });
        if(close) close.addEventListener('click', function(){ menu.classList.add('hidden'); document.body.style.overflow='auto'; });
        links.forEach(function(link){ link.addEventListener('click', function(){ menu.classList.add('hidden'); document.body.style.overflow='auto'; }); });
    })();
</script>
