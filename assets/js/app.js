/**
 * DNHS Hub - Main JavaScript
 * 
 * Application-wide JavaScript functions
 */

var APP_URL = window.location.origin + '/dnhs-hub';

$(document).ready(function() {
    // Initialize DataTables - only on tables that exist and have thead
    if ($.fn.DataTable) {
        $('.data-table').each(function() {
            var $table = $(this);
            if ($table.find('thead').length && $table.find('tbody tr').length) {
                try {
                    $table.DataTable({
                        responsive: true,
                        pageLength: 10,
                        order: [[0, 'desc']],
                        language: {
                            search: "Search:",
                            lengthMenu: "Show _MENU_ entries",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            paginate: {
                                first: "First",
                                last: "Last",
                                next: "Next",
                                previous: "Previous"
                            },
                            emptyTable: "No data available",
                            zeroRecords: "No matching records found"
                        }
                    });
                } catch(e) {
                    console.log('DataTables init skipped:', e.message);
                }
            }
        });
    }
    
    // Sidebar Toggle
    $('#sidebarToggle').on('click', function() {
        $('#sidebar').toggleClass('show');
    });
    
    // Close sidebar on mobile when clicking outside
    $(document).on('click', function(e) {
        if ($(window).width() < 992) {
            if (!$(e.target).closest('.sidebar, #sidebarToggle').length) {
                $('#sidebar').removeClass('show');
            }
        }
    });
    
    // Confirm delete actions
    $(document).on('click', '.btn-delete', function(e) {
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
    
    // Confirm archive actions
    $(document).on('click', '.btn-archive', function(e) {
        if (!confirm('Are you sure you want to archive this item?')) {
            e.preventDefault();
        }
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);
    
    // Mark notification as read
    $(document).on('click', '.notification-item', function() {
        var notifId = $(this).data('id');
        if (notifId) {
            $.post(APP_URL + '/notifications/mark_read.php', { id: notifId });
            $(this).removeClass('bg-light');
        }
    });
    
    // Print functionality
    $(document).on('click', '.btn-print', function() {
        window.print();
    });
    
    // File upload preview
    $(document).on('change', '.file-input', function() {
        var file = this.files[0];
        var preview = $(this).closest('.mb-3').find('.file-preview');
        
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                if (file.type.startsWith('image/')) {
                    preview.html('<img src="' + e.target.result + '" class="img-fluid rounded" style="max-height: 200px;">');
                } else {
                    preview.html('<div class="alert alert-info"><i class="fas fa-file me-2"></i>' + file.name + '</div>');
                }
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Form validation
    $(document).on('submit', 'form.needs-validation', function(e) {
        var form = this;
        
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        $(form).addClass('was-validated');
    });
    
    // Status update via dropdown
    $(document).on('change', '.status-select', function() {
        var select = $(this);
        var requestId = select.data('request-id');
        var newStatus = select.val();
        
        if (confirm('Update status to "' + newStatus + '"?')) {
            $.post(APP_URL + '/requests/update_status.php', {
                request_id: requestId,
                status: newStatus
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error updating status: ' + response.message);
                }
            }, 'json');
        } else {
            select.val(select.data('original-value'));
        }
    });
});

/**
 * Format date to readable string
 * 
 * @param {string} dateStr Date string
 * @returns {string} Formatted date
 */
function formatDate(dateStr) {
    if (!dateStr) return '';
    var date = new Date(dateStr);
    var options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

/**
 * Show loading spinner
 * 
 * @param {string} elementId Element ID to show spinner
 */
function showLoading(elementId) {
    var element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2">Loading...</p></div>';
    }
}

/**
 * Confirm action with custom message
 * 
 * @param {string} message Confirmation message
 * @returns {boolean} User confirmation
 */
function confirmAction(message) {
    return confirm(message || 'Are you sure you want to proceed?');
}

/**
 * Print element
 * 
 * @param {string} elementId Element to print
 */
function printElement(elementId) {
    var content = document.getElementById(elementId);
    if (!content) return;
    
    var printWindow = window.open('', '_blank');
    printWindow.document.write('<html><head><title>Print</title>');
    printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
    printWindow.document.write('</head><body class="p-4">');
    printWindow.document.write(content.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}
