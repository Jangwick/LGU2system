<?php
/**
 * Guest Footer
 * Shared footer for public/guest-accessible pages
 * Provides consistent navigation links and legal information
 */
?>

<!-- Guest Footer -->
<footer class="bg-white border-t border-gray-100 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="flex flex-col md:flex-row justify-between items-start gap-10 md:gap-0">
            <!-- Logo & Description -->
            <div class="w-full md:w-auto text-center md:text-left">
                <div class="flex items-center justify-center md:justify-start mb-4">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Logo" class="h-9 w-9 mr-3 shadow-sm rounded-full">
                    <div class="text-gray-900 font-black text-xl tracking-tighter">VALENZUELA<span class="text-red-600">LRMS</span></div>
                </div>
                <p class="text-gray-400 font-bold text-[10px] uppercase tracking-[0.15em] max-w-xs mx-auto md:mx-0 leading-relaxed">
                    Official Legislative Records Management System.<br>
                    City Government of Valenzuela.
                </p>
            </div>
            
            <!-- Quick Links -->
            <div class="w-full md:w-auto grid grid-cols-2 gap-8 sm:gap-12 md:gap-20">
                <div class="text-center md:text-left">
                    <h4 class="text-gray-900 font-black uppercase tracking-widest text-[10px] mb-4">Navigate</h4>
                    <ul class="space-y-3 text-xs font-bold text-gray-500">
                        <li><a href="<?php echo BASE_URL; ?>/index.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">Home</a></li>
                        <li><a href="<?php echo HELP_URL; ?>/views/faq.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">FAQ</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/news.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">News & Updates</a></li>
                        <li><a href="<?php echo HELP_URL; ?>/views/contact.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">Support</a></li>
                    </ul>
                </div>
                <div class="text-center md:text-left">
                    <h4 class="text-gray-900 font-black uppercase tracking-widest text-[10px] mb-4">Legal</h4>
                    <ul class="space-y-3 text-xs font-bold text-gray-500">
                        <li><a href="<?php echo HELP_URL; ?>/views/privacy.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">Privacy Policy</a></li>
                        <li><a href="<?php echo HELP_URL; ?>/views/terms.php" class="hover:text-red-600 transition-colors uppercase tracking-wider">Terms of Service</a></li>
                        <li><a href="<?php echo LOGIN_URL; ?>" class="hover:text-red-600 transition-colors uppercase tracking-wider">Sign In</a></li>
                        <li><a href="<?php echo REGISTER_URL; ?>" class="hover:text-red-600 transition-colors uppercase tracking-wider">Register</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="mt-10 pt-6 border-t border-gray-50 flex flex-col md:flex-row justify-between items-center text-[9px] text-gray-400 font-black uppercase tracking-[0.2em] text-center md:text-left">
            <div class="mb-2 md:mb-0">&copy; <?php echo date('Y'); ?> City of Valenzuela. All rights reserved.</div>
            <div>Legislative Records Department</div>
        </div>
    </div>
</footer>
