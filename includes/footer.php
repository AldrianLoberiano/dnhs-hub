            </div>
            <!-- End Main Content -->
        </div>
        <!-- End Page Content Wrapper -->
    </div>
    <!-- End Wrapper -->
    
    <!-- Toast Alert Container -->
    <div class="toast-container" id="toastContainer"></div>
    
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/app.js"></script>
    
    <?php
    // Auto-show flash message as toast
    $flash = getFlashMessage();
    if ($flash):
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast('<?php echo $flash["type"]; ?>', '<?php echo addslashes($flash["message"]); ?>');
    });
    </script>
    <?php endif; ?>
    
    <?php if (isset($extraScripts)): ?>
        <?php echo $extraScripts; ?>
    <?php endif; ?>
</body>
</html>
