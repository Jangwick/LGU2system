    </div><!-- End of main flex container -->
    
    <!-- Footer -->
    <script src="<?php echo asset('js/main.js'); ?>"></script>
    
    <?php if (isset($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
