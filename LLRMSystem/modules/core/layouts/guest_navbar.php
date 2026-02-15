<?php
/**
 * Guest Navigation Bar
 * Shared navigation component for public/guest-accessible pages
 * (FAQ, Privacy, Terms, Contact, News, etc.)
 * 
 * Usage: Set $currentGuestPage before including this file
 * Available values: 'home', 'faq', 'privacy', 'terms', 'contact', 'news'
 */

$currentGuestPage = $currentGuestPage ?? '';
?>

<!-- Guest Navigation -->
<nav id="guest-nav" class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-gray-100 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 md:h-[72px]">
            
            <!-- Logo -->
            <a href="<?php echo BASE_URL; ?>/index.php" class="flex items-center group cursor-pointer flex-shrink-0">
                <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Logo" class="h-8 w-8 md:h-10 md:w-10 mr-2 md:mr-3 transition-transform duration-500 group-hover:rotate-12 shadow-sm rounded-full">
                <div class="flex flex-col">
                    <span class="text-lg md:text-xl font-black text-gray-900 tracking-tighter leading-none">VALENZUELA<span class="text-red-600">LRMS</span></span>
                    <span class="hidden md:block text-[8px] text-gray-400 font-bold uppercase tracking-[0.2em] leading-none mt-0.5">Legislative Office</span>
                </div>
            </a>

            <!-- Desktop Navigation Links -->
            <div class="hidden lg:flex items-center space-x-1">
                <a href="<?php echo BASE_URL; ?>/index.php" 
                   class="px-3 py-2 rounded-lg text-sm font-bold transition-all duration-200 <?php echo $currentGuestPage === 'home' ? 'text-red-600 bg-red-50' : 'text-gray-600 hover:text-red-600 hover:bg-gray-50'; ?>">
                    <i class="bi bi-house-door mr-1"></i>Home
                </a>
                <a href="<?php echo HELP_URL; ?>/views/faq.php" 
                   class="px-3 py-2 rounded-lg text-sm font-bold transition-all duration-200 <?php echo $currentGuestPage === 'faq' ? 'text-red-600 bg-red-50' : 'text-gray-600 hover:text-red-600 hover:bg-gray-50'; ?>">
                    <i class="bi bi-patch-question mr-1"></i>FAQ
                </a>
                <a href="<?php echo HELP_URL; ?>/views/contact.php" 
                   class="px-3 py-2 rounded-lg text-sm font-bold transition-all duration-200 <?php echo $currentGuestPage === 'contact' ? 'text-red-600 bg-red-50' : 'text-gray-600 hover:text-red-600 hover:bg-gray-50'; ?>">
                    <i class="bi bi-headset mr-1"></i>Support
                </a>
                
                <span class="h-6 w-px bg-gray-200 mx-2"></span>
                
                <a href="<?php echo HELP_URL; ?>/views/privacy.php" 
                   class="px-3 py-2 rounded-lg text-xs font-bold transition-all duration-200 <?php echo $currentGuestPage === 'privacy' ? 'text-red-600 bg-red-50' : 'text-gray-400 hover:text-red-600 hover:bg-gray-50'; ?> uppercase tracking-wider">
                    Privacy
                </a>
                <a href="<?php echo HELP_URL; ?>/views/terms.php" 
                   class="px-3 py-2 rounded-lg text-xs font-bold transition-all duration-200 <?php echo $currentGuestPage === 'terms' ? 'text-red-600 bg-red-50' : 'text-gray-400 hover:text-red-600 hover:bg-gray-50'; ?> uppercase tracking-wider">
                    Terms
                </a>
            </div>

            <!-- Right Side: Auth Buttons -->
            <div class="flex items-center space-x-2 md:space-x-3">
                <a href="<?php echo LOGIN_URL; ?>" class="hidden sm:inline-flex text-gray-600 hover:text-red-600 font-bold px-3 py-2 text-sm transition-all whitespace-nowrap">
                    Sign In
                </a>
                <a href="<?php echo REGISTER_URL; ?>" class="bg-red-600 hover:bg-red-700 text-white font-black px-4 md:px-5 py-2 md:py-2.5 rounded-full text-xs md:text-sm shadow-lg shadow-red-200/50 transition-all hover:scale-105 whitespace-nowrap">
                    Get Started
                </a>
                
                <!-- Mobile Menu Button -->
                <button id="guest-mobile-toggle" class="lg:hidden p-2 text-gray-600 hover:text-red-600 hover:bg-gray-50 rounded-lg transition-all ml-1" aria-label="Open menu">
                    <i class="bi bi-list text-xl"></i>
                </button>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile Menu Overlay -->
<div id="guest-mobile-menu" class="hidden fixed inset-0 z-[200] bg-white overflow-y-auto lg:hidden">
    <div class="sticky top-0 w-full p-4 flex justify-between items-center border-b border-gray-100 bg-white/95 backdrop-blur-md z-10">
        <span class="text-xl font-black text-gray-900 tracking-tighter">VALENZUELA<span class="text-red-600">LRMS</span></span>
        <button id="guest-mobile-close" class="p-2 text-gray-500 hover:text-red-600 transition-colors rounded-lg hover:bg-gray-50" aria-label="Close menu">
            <i class="bi bi-x-lg text-2xl"></i>
        </button>
    </div>
    
    <div class="pt-8 pb-12 px-8 flex flex-col items-center space-y-2 text-center">
        <h3 class="text-gray-400 font-black uppercase tracking-widest text-[10px] mb-4">Navigate</h3>
        <div class="h-px w-8 bg-red-600/20 mb-4"></div>
        
        <a href="<?php echo BASE_URL; ?>/index.php" 
           class="guest-mobile-link w-full py-4 px-6 rounded-xl text-lg font-black uppercase tracking-tight transition-all <?php echo $currentGuestPage === 'home' ? 'text-red-600 bg-red-50' : 'text-gray-800 hover:text-red-600 hover:bg-gray-50'; ?>">
            <i class="bi bi-house-door mr-3"></i>Home
        </a>
        <a href="<?php echo HELP_URL; ?>/views/faq.php" 
           class="guest-mobile-link w-full py-4 px-6 rounded-xl text-lg font-black uppercase tracking-tight transition-all <?php echo $currentGuestPage === 'faq' ? 'text-red-600 bg-red-50' : 'text-gray-800 hover:text-red-600 hover:bg-gray-50'; ?>">
            <i class="bi bi-patch-question mr-3"></i>FAQ
        </a>
        <a href="<?php echo HELP_URL; ?>/views/contact.php" 
           class="guest-mobile-link w-full py-4 px-6 rounded-xl text-lg font-black uppercase tracking-tight transition-all <?php echo $currentGuestPage === 'contact' ? 'text-red-600 bg-red-50' : 'text-gray-800 hover:text-red-600 hover:bg-gray-50'; ?>">
            <i class="bi bi-headset mr-3"></i>Support & Contact
        </a>
        
        <div class="w-full h-px bg-gray-100 my-4"></div>
        <h3 class="text-gray-400 font-black uppercase tracking-widest text-[10px] mb-2">Legal</h3>
        
        <a href="<?php echo HELP_URL; ?>/views/privacy.php" 
           class="guest-mobile-link w-full py-3 px-6 rounded-xl text-sm font-bold uppercase tracking-wider transition-all <?php echo $currentGuestPage === 'privacy' ? 'text-red-600 bg-red-50' : 'text-gray-500 hover:text-red-600 hover:bg-gray-50'; ?>">
            <i class="bi bi-shield-lock mr-3"></i>Privacy Policy
        </a>
        <a href="<?php echo HELP_URL; ?>/views/terms.php" 
           class="guest-mobile-link w-full py-3 px-6 rounded-xl text-sm font-bold uppercase tracking-wider transition-all <?php echo $currentGuestPage === 'terms' ? 'text-red-600 bg-red-50' : 'text-gray-500 hover:text-red-600 hover:bg-gray-50'; ?>">
            <i class="bi bi-file-earmark-ruled mr-3"></i>Terms of Service
        </a>
        
        <div class="pt-6 w-full border-t border-gray-100 mt-4 flex flex-col space-y-3">
            <a href="<?php echo LOGIN_URL; ?>" class="w-full py-3 text-center text-gray-600 font-black uppercase tracking-widest text-sm rounded-xl border border-gray-200 hover:border-red-200 hover:text-red-600 transition-all">Sign In</a>
            <a href="<?php echo REGISTER_URL; ?>" class="w-full py-4 text-center bg-red-600 text-white font-black uppercase tracking-widest text-sm rounded-xl shadow-xl shadow-red-200 hover:bg-red-700 transition-all">Get Started</a>
        </div>
    </div>
</div>

<script>
(function() {
    const toggle = document.getElementById('guest-mobile-toggle');
    const close = document.getElementById('guest-mobile-close');
    const menu = document.getElementById('guest-mobile-menu');
    const links = document.querySelectorAll('.guest-mobile-link');
    
    function openMenu() {
        menu.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeMenu() {
        menu.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    
    if (toggle) toggle.addEventListener('click', openMenu);
    if (close) close.addEventListener('click', closeMenu);
    links.forEach(function(link) { link.addEventListener('click', closeMenu); });
    
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeMenu();
    });
    
    // Scroll effect on nav
    var nav = document.getElementById('guest-nav');
    if (nav) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 20) {
                nav.classList.add('shadow-md', 'bg-white/95');
                nav.classList.remove('bg-white/80');
            } else {
                nav.classList.remove('shadow-md', 'bg-white/95');
                nav.classList.add('bg-white/80');
            }
        });
    }
})();
</script>
