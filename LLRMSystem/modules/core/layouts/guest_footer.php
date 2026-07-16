<?php
/**
 * Guest Footer
 * Shared footer for public/guest-accessible pages
 * Provides consistent navigation links and legal information
 */
?>

<!-- Guest Footer -->
<footer class="bg-white dark:bg-[#1a1a2e] border-t border-gray-100 dark:border-[#2a2a3e] mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="flex flex-col md:flex-row justify-between items-start gap-10 md:gap-0">
            <!-- Logo & Description -->
            <div class="w-full md:w-auto text-center md:text-left">
                <div class="flex items-center justify-center md:justify-start mb-4">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Logo" class="h-9 w-9 mr-3 shadow-sm rounded-full">
                    <div class="text-gray-900 dark:text-gray-100 font-black text-xl tracking-tighter">VALENZUELA<span class="text-red-600">LRMS</span></div>
                </div>
                <p class="text-gray-400 dark:text-gray-500 font-bold text-[10px] uppercase tracking-[0.15em] max-w-xs mx-auto md:mx-0 leading-relaxed">
                    Official Legislative Records Management System.<br>
                    City Government of Valenzuela.
                </p>
            </div>
            
            <!-- Quick Links -->
            <div class="w-full md:w-auto grid grid-cols-2 gap-8 sm:gap-12 md:gap-20">
                <div class="text-center md:text-left">
                    <h4 class="text-gray-900 dark:text-gray-200 font-black uppercase tracking-widest text-[10px] mb-4">Navigate</h4>
                    <ul class="space-y-3 text-xs font-bold text-gray-500 dark:text-gray-400">
                        <li><a href="<?php echo BASE_URL; ?>/index.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">Home</a></li>
                        <li><a href="<?php echo HELP_URL; ?>/views/faq.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">FAQ</a></li>
                        <li><a href="<?php echo HELP_URL; ?>/views/contact.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">Support</a></li>
                    </ul>
                </div>
                <div class="text-center md:text-left">
                    <h4 class="text-gray-900 dark:text-gray-200 font-black uppercase tracking-widest text-[10px] mb-4">Legal</h4>
                    <ul class="space-y-3 text-xs font-bold text-gray-500 dark:text-gray-400">
                        <li><a href="<?php echo HELP_URL; ?>/views/privacy.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">Privacy Policy</a></li>
                        <li><a href="<?php echo HELP_URL; ?>/views/terms.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">Terms of Service</a></li>
                        <li><a href="<?php echo LOGIN_URL; ?>" class="hover:text-red-600 transition-colors uppercase tracking-wider">Sign In</a></li>
                        <li><a href="<?php echo REGISTER_URL; ?>" class="hover:text-red-600 transition-colors uppercase tracking-wider">Register</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="mt-10 pt-6 border-t border-gray-50 dark:border-[#2a2a3e] flex flex-col md:flex-row justify-between items-center text-[9px] text-gray-400 dark:text-gray-500 font-black uppercase tracking-[0.2em] text-center md:text-left">
            <div class="mb-2 md:mb-0">&copy; <?php echo date('Y'); ?> City of Valenzuela. All rights reserved.</div>
            <div>Legislative Records Department</div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="back-to-top" class="no-ripple fixed z-[40] bg-red-600 text-white rounded-full border-3 border-white cursor-pointer shadow-lg shadow-red-600/50 items-center justify-center transition-all duration-300 hover:bg-red-700 hover:scale-110 active:scale-95"
        style="display: none; position: fixed; bottom: 2rem; right: 1.5rem; left: auto; width: 3.5rem; height: 3.5rem;"
        title="Back to top"
        aria-label="Scroll to top">
    <i class="bi bi-arrow-up text-2xl leading-none pointer-events-none"></i>
</button>

<script>
// Back to Top Button - Immediate execution
(function() {
    var btn = document.getElementById('back-to-top');
    if (!btn) return;

    function checkScroll() {
        // Don't show back-to-top when any modal is open (body overflow hidden)
        if (document.body.style.overflow === 'hidden') {
            btn.style.display = 'none';
            return;
        }
        var scrolled = false;

        // Check window scroll
        if (window.pageYOffset > 100 || document.documentElement.scrollTop > 100) {
            scrolled = true;
        }

        // Check main element scroll
        var main = document.querySelector('main');
        if (main && main.scrollTop > 100) {
            scrolled = true;
        }

        // Check any overflow-y-auto elements
        var scrollables = document.querySelectorAll('.overflow-y-auto');
        scrollables.forEach(function(el) {
            if (el.scrollTop > 100) {
                scrolled = true;
            }
        });

        btn.style.display = scrolled ? 'flex' : 'none';
    }

    function scrollToTop() {
        // Scroll window
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Scroll main element
        var main = document.querySelector('main');
        if (main) main.scrollTo({ top: 0, behavior: 'smooth' });

        // Scroll any overflow-y-auto elements
        document.querySelectorAll('.overflow-y-auto').forEach(function(el) {
            el.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Add click handler
    btn.onclick = scrollToTop;

    // Listen for scroll on window
    window.addEventListener('scroll', checkScroll, { passive: true });

    // Listen for scroll on main
    var main = document.querySelector('main');
    if (main) {
        main.addEventListener('scroll', checkScroll, { passive: true });
    }

    // Listen for scroll on overflow-y-auto elements
    document.querySelectorAll('.overflow-y-auto').forEach(function(el) {
        el.addEventListener('scroll', checkScroll, { passive: true });
    });

    // Initial check
    checkScroll();

    // MutationObserver: hide back-to-top immediately when any modal opens
    var bodyObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mut) {
            if (mut.attributeName === 'style') {
                if (document.body.style.overflow === 'hidden') {
                    btn.style.display = 'none';
                } else {
                    checkScroll();
                }
            }
        });
    });
    bodyObserver.observe(document.body, { attributes: true, attributeFilter: ['style'] });

    var modalObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mut) {
            if (mut.attributeName === 'class') {
                var el = mut.target;
                var isHidden = el.classList.contains('hidden');
                if (!isHidden) {
                    btn.style.display = 'none';
                } else {
                    checkScroll();
                }
            }
        });
    });
    document.querySelectorAll('[id*="modal"]').forEach(function(el) {
        modalObserver.observe(el, { attributes: true, attributeFilter: ['class'] });
    });
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[id*="modal"]').forEach(function(el) {
            modalObserver.observe(el, { attributes: true, attributeFilter: ['class'] });
        });
    });
})();
</script>
