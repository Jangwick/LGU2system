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
<nav id="guest-nav" class="bg-white/80 dark:bg-[#1a1a2e]/90 backdrop-blur-md sticky top-0 z-50 border-b border-gray-100 dark:border-[#2a2a3e] transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 md:h-[72px] gap-3">
            
            <!-- Logo -->
            <a href="<?php echo BASE_URL; ?>/index.php" class="flex items-center group cursor-pointer min-w-0 flex-shrink">
                <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Logo" class="h-9 w-9 md:h-10 md:w-10 mr-2 md:mr-3 transition-transform duration-500 group-hover:rotate-12 shadow-sm rounded-full flex-shrink-0">
                <div class="flex flex-col min-w-0">
                    <span class="text-[15px] md:text-xl font-black text-gray-900 dark:text-gray-100 tracking-tighter leading-none truncate">VALENZUELA<span class="text-red-600">LRMS</span></span>
                    <span class="hidden md:block text-[8px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-[0.2em] leading-none mt-0.5">Legislative Office</span>
                </div>
            </a>

            <!-- Desktop Navigation Links -->
            <div class="hidden lg:flex items-center space-x-1">
                <a href="<?php echo BASE_URL; ?>/index.php" 
                   class="px-3 py-2 rounded-lg text-sm font-bold transition-all duration-200 <?php echo $currentGuestPage === 'home' ? 'text-red-600 bg-red-50 dark:bg-red-600/15' : 'text-gray-600 dark:text-gray-300 hover:text-red-600 hover:bg-gray-50 dark:hover:bg-white/10'; ?>">
                    <i class="bi bi-house-door mr-1"></i>Home
                </a>
                <a href="<?php echo HELP_URL; ?>/views/faq.php" 
                   class="px-3 py-2 rounded-lg text-sm font-bold transition-all duration-200 <?php echo $currentGuestPage === 'faq' ? 'text-red-600 bg-red-50 dark:bg-red-600/15' : 'text-gray-600 dark:text-gray-300 hover:text-red-600 hover:bg-gray-50 dark:hover:bg-white/10'; ?>">
                    <i class="bi bi-patch-question mr-1"></i>FAQ
                </a>
                <a href="<?php echo HELP_URL; ?>/views/contact.php" 
                   class="px-3 py-2 rounded-lg text-sm font-bold transition-all duration-200 <?php echo $currentGuestPage === 'contact' ? 'text-red-600 bg-red-50 dark:bg-red-600/15' : 'text-gray-600 dark:text-gray-300 hover:text-red-600 hover:bg-gray-50 dark:hover:bg-white/10'; ?>">
                    <i class="bi bi-headset mr-1"></i>Support
                </a>
                
                <span class="h-6 w-px bg-gray-200 dark:bg-gray-600 mx-2"></span>
                
                <a href="<?php echo HELP_URL; ?>/views/privacy.php" 
                   class="px-3 py-2 rounded-lg text-xs font-bold transition-all duration-200 <?php echo $currentGuestPage === 'privacy' ? 'text-red-600 bg-red-50 dark:bg-red-600/15' : 'text-gray-400 dark:text-gray-400 hover:text-red-600 hover:bg-gray-50 dark:hover:bg-white/10'; ?> uppercase tracking-wider">
                    Privacy
                </a>
                <a href="<?php echo HELP_URL; ?>/views/terms.php" 
                   class="px-3 py-2 rounded-lg text-xs font-bold transition-all duration-200 <?php echo $currentGuestPage === 'terms' ? 'text-red-600 bg-red-50 dark:bg-red-600/15' : 'text-gray-400 dark:text-gray-400 hover:text-red-600 hover:bg-gray-50 dark:hover:bg-white/10'; ?> uppercase tracking-wider">
                    Terms
                </a>
            </div>

            <!-- Right Side: Auth Buttons -->
            <div class="flex items-center gap-2 md:gap-3 flex-shrink-0">
                <a href="<?php echo LOGIN_URL; ?>" class="hidden md:inline-flex text-gray-600 dark:text-gray-300 hover:text-red-600 font-bold px-3 py-2 text-sm transition-all whitespace-nowrap">
                    Sign In
                </a>
                <a href="<?php echo REGISTER_URL; ?>" class="hidden md:inline-flex bg-red-600 hover:bg-red-700 text-white font-black px-4 md:px-5 py-2 md:py-2.5 rounded-full text-xs md:text-sm shadow-lg shadow-red-200/50 transition-all hover:scale-105 whitespace-nowrap">
                    Get Started
                </a>
                
                <!-- Dark/Light Mode Toggle -->
                <button id="guest-darkmode-btn" onclick="toggleGuestDarkMode()" class="hero-toggle-btn w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 bg-white transition-all shadow-sm" aria-label="Toggle dark mode">
                    <i class="bi bi-moon-fill guest-dark-icon text-gray-500 text-lg"></i>
                    <i class="bi bi-sun-fill guest-light-icon text-yellow-400 text-lg" style="display:none"></i>
                </button>
                
                <!-- Mobile Menu Button -->
                <button id="guest-mobile-toggle" class="hero-toggle-btn lg:hidden w-10 h-10 flex items-center justify-center text-gray-700 hover:text-red-600 rounded-lg transition-all border border-gray-300 bg-white shadow-sm" aria-label="Open menu">
                    <i class="bi bi-list text-xl"></i>
                </button>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile Menu Overlay -->
<div id="guest-mobile-menu" style="display:none; position:fixed; inset:0; z-index:200; background:#fff; flex-direction:column;" class="lg:hidden">
    <!-- Fixed Header -->
    <div style="flex-shrink:0; padding:1rem; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f3f4f6; background:#fff;">
        <span class="text-xl font-black text-gray-900 tracking-tighter">VALENZUELA<span class="text-red-600">LRMS</span></span>
        <button id="guest-mobile-close" class="hero-toggle-btn p-2 text-gray-500 hover:text-red-600 transition-colors rounded-lg hover:bg-gray-50" aria-label="Close menu">
            <i class="bi bi-x-lg text-2xl"></i>
        </button>
    </div>
    
    <!-- Scrollable Content -->
    <div style="flex:1; overflow-y:auto;" class="pt-8 pb-12 px-8 flex flex-col items-center space-y-2 text-center">
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
// Guest dark mode toggle
function toggleGuestDarkMode() {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    
    if (isDark) {
        html.classList.remove('dark');
        html.style.colorScheme = 'light';
        localStorage.setItem('theme', 'light');
    } else {
        html.classList.add('dark');
        html.style.colorScheme = 'dark';
        localStorage.setItem('theme', 'dark');
    }
    updateGuestThemeButton();
    
    // Sync with main theme toggle if it exists
    const mainToggle = document.getElementById('theme-toggle');
    if (mainToggle) mainToggle.click();
}

function updateGuestThemeButton() {
    const isDark = document.documentElement.classList.contains('dark');
    const darkIcons = document.querySelectorAll('.guest-dark-icon');
    const lightIcons = document.querySelectorAll('.guest-light-icon');
    const label = document.getElementById('guest-theme-label');
    
    darkIcons.forEach(i => i.style.display = isDark ? 'none' : '');
    lightIcons.forEach(i => i.style.display = isDark ? '' : 'none');
    if (label) label.textContent = isDark ? 'Light Mode' : 'Dark Mode';

    // Force styles via JS to override global .dark button !important rules
    const themeBtn = document.getElementById('guest-darkmode-btn');
    const hamburgerBtn = document.getElementById('guest-mobile-toggle');
    if (themeBtn) {
        themeBtn.style.backgroundColor = isDark ? '#1f2937' : '#fff';
        themeBtn.style.borderColor = isDark ? '#4b5563' : '#e5e7eb';
        themeBtn.style.color = isDark ? '#facc15' : '#6b7280';
    }
    if (hamburgerBtn) {
        hamburgerBtn.style.backgroundColor = isDark ? '#1f2937' : '#fff';
        hamburgerBtn.style.borderColor = isDark ? '#4b5563' : '#d1d5db';
        hamburgerBtn.style.color = isDark ? '#d1d5db' : '#374151';
    }
}

// Init on load
document.addEventListener('DOMContentLoaded', updateGuestThemeButton);

(function() {
    const toggle = document.getElementById('guest-mobile-toggle');
    const close = document.getElementById('guest-mobile-close');
    const menu = document.getElementById('guest-mobile-menu');
    const links = document.querySelectorAll('.guest-mobile-link');
    
    // Move the mobile menu to document.body so it's not trapped by parent stacking contexts
    if (menu) {
        document.body.appendChild(menu);
    }
    
    function openMenu() {
        var isDark = document.documentElement.classList.contains('dark');
        menu.style.display = 'flex';
        menu.style.background = isDark ? '#1a1a2e' : '#fff';
        // Update header inside menu
        var header = menu.querySelector('div[style*="flex-shrink"]');
        if (header) {
            header.style.background = isDark ? '#1a1a2e' : '#fff';
            header.style.borderBottomColor = isDark ? '#2a2a3e' : '#f3f4f6';
        }
        // Style close button for dark mode
        var closeBtn = document.getElementById('guest-mobile-close');
        if (closeBtn) {
            closeBtn.style.color = isDark ? '#9ca3af' : '';
            closeBtn.style.backgroundColor = 'transparent';
        }
        // Style mobile links and dividers
        menu.querySelectorAll('.guest-mobile-link').forEach(function(link) {
            if (!link.classList.contains('text-red-600')) {
                link.style.color = isDark ? '#d1d5db' : '';
            }
        });
        menu.querySelectorAll('.bg-gray-100').forEach(function(el) {
            el.style.backgroundColor = isDark ? '#2a2a3e' : '';
        });
        // Style sign-in button border
        var signInBtn = menu.querySelector('a[href*="login"]');
        if (signInBtn) {
            signInBtn.style.color = isDark ? '#d1d5db' : '';
            signInBtn.style.borderColor = isDark ? '#374151' : '';
        }
        document.body.style.overflow = 'hidden';
    }
    
    function closeMenu() {
        menu.style.display = 'none';
        document.body.style.overflow = '';
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
            var isDark = document.documentElement.classList.contains('dark');
            if (window.scrollY > 20) {
                nav.classList.add('shadow-md');
                nav.classList.remove('bg-white/80');
                nav.style.backgroundColor = isDark ? 'rgba(26,26,46,0.97)' : 'rgba(255,255,255,0.95)';
            } else {
                nav.classList.remove('shadow-md');
                nav.classList.add('bg-white/80');
                nav.style.backgroundColor = isDark ? 'rgba(26,26,46,0.85)' : '';
            }
        });
    }
})();
</script>
