            </div>
            <!-- End Main Content -->
            
            <!-- Footer -->
            <footer class="app-footer">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <span class="footer-brand">DNHS Hub</span>
                            <span class="footer-divider">|</span>
                            <span class="footer-text">Dayap National High School</span>
                        </div>
                        <div class="col-md-4 text-center">
                            <span class="footer-text">&copy; <?php echo date('Y'); ?> Registrar's Office. All rights reserved.</span>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="#" class="footer-link">Help</a>
                            <a href="#" class="footer-link">Privacy</a>
                            <a href="#" class="footer-link">Terms</a>
                        </div>
                    </div>
                </div>
            </footer>
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
        showToast(<?php echo json_encode($flash["type"]); ?>, <?php echo json_encode($flash["message"]); ?>);
    });
    </script>
    <?php endif; ?>
    
    <?php if (isset($extraScripts)): ?>
        <?php echo $extraScripts; ?>
    <?php endif; ?>
</body>
</html>
