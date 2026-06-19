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
    
    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" id="confirmModalHeader">
                    <h5 class="modal-title" id="confirmModalTitle">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="confirm-icon mb-3" id="confirmModalIcon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <p class="mb-0" id="confirmModalMessage" style="font-size: 15px; color: #495057;">Are you sure?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn px-3" id="confirmModalBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>
    
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
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logout confirmation
        var logoutBtn = document.getElementById('btnLogout');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                var href = this.getAttribute('href');
                showConfirmModal({
                    title: 'Confirm Logout',
                    message: 'Are you sure you want to logout from your account?',
                    type: 'danger',
                    icon: 'fas fa-sign-out-alt',
                    confirmText: 'Logout',
                    onConfirm: function() { window.location.href = href; }
                });
            });
        }
        
        // Delete confirmation
        document.querySelectorAll('.btn-confirm-delete').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var form = this.closest('form');
                showConfirmModal({
                    title: 'Confirm Delete',
                    message: 'Are you sure you want to delete this item? This action cannot be undone.',
                    type: 'danger',
                    icon: 'fas fa-trash-alt',
                    confirmText: 'Delete',
                    onConfirm: function() { form.submit(); }
                });
            });
        });
        
        // Archive confirmation
        document.querySelectorAll('.btn-confirm-archive').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var form = this.closest('form');
                showConfirmModal({
                    title: 'Confirm Archive',
                    message: 'Are you sure you want to archive this student record?',
                    type: 'warning',
                    icon: 'fas fa-archive',
                    confirmText: 'Archive',
                    onConfirm: function() { form.submit(); }
                });
            });
        });
        
        // Restore confirmation
        document.querySelectorAll('.btn-confirm-restore').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var form = this.closest('form');
                showConfirmModal({
                    title: 'Confirm Restore',
                    message: 'Are you sure you want to restore this student record?',
                    type: 'success',
                    icon: 'fas fa-undo',
                    confirmText: 'Restore',
                    onConfirm: function() { form.submit(); }
                });
            });
        });
        
        // Toggle status confirmation
        document.querySelectorAll('.btn-confirm-toggle').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var form = this.closest('form');
                var action = this.dataset.action || 'toggle';
                showConfirmModal({
                    title: 'Confirm ' + action,
                    message: 'Are you sure you want to ' + action.toLowerCase() + ' this user?',
                    type: action === 'Deactivate' ? 'danger' : 'success',
                    icon: action === 'Deactivate' ? 'fas fa-ban' : 'fas fa-check',
                    confirmText: action,
                    onConfirm: function() { form.submit(); }
                });
            });
        });
    });
    </script>
</body>
</html>
