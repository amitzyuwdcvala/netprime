/**
 * NetPrime Admin Common JS
 * ========================
 * Standard AJAX patterns for admin panel operations.
 * - Offcanvas / Modal form loading
 * - AJAX form submission
 * - Delete with SweetAlert confirmation
 * - DataTable reload utility
 */

(function ($) {
    'use strict';

    // CSRF Token for all AJAX requests
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': csrfToken }
    });

    // =========================================================================
    // 1. LOAD CANVAS / MODAL FORM (Create & Edit)
    // =========================================================================
    $(document).on('click', '.add-record-btn, .edit-record-btn', function (e) {
        e.preventDefault();

        var $this = $(this);
        var $container = $this.closest('[data-manage-route]');
        var manageRoute = $container.data('manage-route');
        var recordId = $this.data('id') || '';
        var canvasId = $container.closest('.section-body').siblings('.section-header').length
            ? '#manage-record'
            : '#manage-record';

        // Find the modal in the page
        var $modal = $container.find('.modal').first();
        if ($modal.length === 0) {
            $modal = $('#manage-record');
        }

        $.ajax({
            url: manageRoute,
            type: 'POST',
            data: { id: recordId, _token: csrfToken },
            beforeSend: function () {
                $modal.find('.modal-body').html(
                    '<div class="text-center py-5"><div class="spinner-border" role="status"></div></div>'
                );
                $modal.modal('show');
            },
            success: function (response) {
                if (response.status === 'success') {
                    $modal.find('.modal-body').html(response.view);
                } else {
                    toastrr(globalAjaxErrorLabel, response.message || globalAjaxErrorMessage, 'error');
                    $modal.modal('hide');
                }
            },
            error: function (xhr) {
                toastrr(globalAjaxErrorLabel, xhr.responseJSON?.message || globalAjaxErrorMessage, 'error');
                $modal.modal('hide');
            }
        });
    });

    // =========================================================================
    // 2. AJAX FORM SUBMISSION (from canvas/modal)
    // =========================================================================
    $(document).on('submit', '[id$="-form-data"]', function (e) {
        e.preventDefault();

        var $form = $(this);

        // Check jQuery Validate if loaded
        if ($.fn.validate && !$form.valid()) {
            return;
        }

        var formData = new FormData(this);
        var submitBtn = $form.find('button[type="submit"]');
        var originalText = submitBtn.html();

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                submitBtn.attr('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm mr-1"></span> Saving...'
                );
            },
            success: function (response) {
                submitBtn.attr('disabled', false).html(originalText);
                if (response.status === 'success') {
                    toastrr(globalAjaxSuccessLabel, response.message, 'success');
                    // Close modal
                    $form.closest('.modal').modal('hide');
                    // Reload all DataTables on page
                    reloadAllDataTables();
                } else {
                    toastrr(globalAjaxErrorLabel, response.message || globalAjaxErrorMessage, 'error');
                }
            },
            error: function (xhr) {
                submitBtn.attr('disabled', false).html(originalText);
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    // Show validation errors
                    var errors = xhr.responseJSON.errors;
                    var firstError = Object.values(errors)[0];
                    toastrr(globalAjaxErrorLabel, Array.isArray(firstError) ? firstError[0] : firstError, 'error');
                } else {
                    toastrr(globalAjaxErrorLabel, xhr.responseJSON?.message || globalAjaxErrorMessage, 'error');
                }
            }
        });
    });

    // =========================================================================
    // 3. DELETE RECORD WITH SWEETALERT CONFIRMATION
    // =========================================================================
    $(document).on('click', '.delete-record-btn', function (e) {
        e.preventDefault();

        var $this = $(this);
        var recordId = $this.data('id');
        var $container = $this.closest('[data-delete-route]');
        var deleteRoute = $container.data('delete-route');

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then(function (result) {
                if (result.isConfirmed) {
                    performDelete(deleteRoute, recordId);
                }
            });
        } else {
            if (confirm('Are you sure you want to delete this record?')) {
                performDelete(deleteRoute, recordId);
            }
        }
    });

    function performDelete(route, id) {
        $.ajax({
            url: route,
            type: 'POST',
            data: { id: id, _token: csrfToken },
            success: function (response) {
                if (response.status === 'success') {
                    toastrr(globalAjaxSuccessLabel, response.message, 'success');
                    reloadAllDataTables();
                } else {
                    toastrr(globalAjaxErrorLabel, response.message || globalAjaxErrorMessage, 'error');
                }
            },
            error: function (xhr) {
                toastrr(globalAjaxErrorLabel, xhr.responseJSON?.message || globalAjaxErrorMessage, 'error');
            }
        });
    }

    // =========================================================================
    // 4. UTILITY FUNCTIONS
    // =========================================================================

    /**
     * Reload a specific DataTable by ID.
     */
    window.reloadDataTable = function (tableId) {
        var $table = $(tableId);
        if ($table.length && $.fn.DataTable.isDataTable(tableId)) {
            $table.DataTable().ajax.reload(null, false);
        }
    };

    /**
     * Reload all DataTables on the page.
     */
    window.reloadAllDataTables = function () {
        $('.dataTable').each(function () {
            if ($.fn.DataTable.isDataTable(this)) {
                $(this).DataTable().ajax.reload(null, false);
            }
        });
    };

    /**
     * Close canvas/modal.
     */
    $(document).on('click', '.canvas-close-btn', function () {
        $(this).closest('.modal').modal('hide');
    });

})(jQuery);
