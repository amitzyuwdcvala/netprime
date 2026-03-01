(function ($) {
    'use strict';

    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': csrfToken }
    });

    $(document).on('click', '.add-record-btn, .edit-record-btn', function (e) {
        e.preventDefault();

        var $this = $(this);
        var $container = $this.closest('[data-manage-route]');
        if ($container.length === 0) {
            $container = $this.closest('.section').find('[data-manage-route]').first();
        }
        var manageRoute = $container.length ? $container.data('manage-route') : null;
        if (!manageRoute) {
            toastrr(globalAjaxErrorLabel, 'Manage route not configured', 'error');
            return;
        }
        var recordId = $this.data('id') || '';
        var canvasId = '#manage-record';

        var $modal = $container.find('.modal').first();
        if ($modal.length === 0) {
            $modal = $(canvasId);
        }
        if ($modal.length === 0) {
            $modal = $this.closest('.section').find('.modal').first();
        }

        $.ajax({
            url: manageRoute,
            type: 'POST',
            data: { id: recordId, _token: csrfToken },
            beforeSend: function () {
                $modal.find('.modal-body').html(
                    '<div class="text-center py-5"><div class="spinner-border" role="status"></div></div>'
                );
                if ($modal.parent()[0] !== document.body) {
                    $modal.appendTo('body');
                }
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


    $(document).on('submit', '[id$="-form-data"]', function (e) {
        e.preventDefault();

        var $form = $(this);

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
                    $form.closest('.modal').modal('hide');
                    reloadAllDataTables();
                } else {
                    toastrr(globalAjaxErrorLabel, response.message || globalAjaxErrorMessage, 'error');
                }
            },
            error: function (xhr) {
                submitBtn.attr('disabled', false).html(originalText);
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    var errors = xhr.responseJSON.errors;
                    var firstError = Object.values(errors)[0];
                    toastrr(globalAjaxErrorLabel, Array.isArray(firstError) ? firstError[0] : firstError, 'error');
                } else {
                    toastrr(globalAjaxErrorLabel, xhr.responseJSON?.message || globalAjaxErrorMessage, 'error');
                }
            }
        });
    });

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

    window.reloadDataTable = function (tableId) {
        var $table = $(tableId);
        if ($table.length && $.fn.DataTable.isDataTable(tableId)) {
            $table.DataTable().ajax.reload(null, false);
        }
    };

    window.reloadAllDataTables = function () {
        $('.dataTable').each(function () {
            if ($.fn.DataTable.isDataTable(this)) {
                $(this).DataTable().ajax.reload(null, false);
            }
        });
    };

    $(document).on('click', '.canvas-close-btn', function () {
        $(this).closest('.modal').modal('hide');
    });

    $(document).on('change', '.is_active_status', function () {
        var checkbox = $(this);
        var id = checkbox.data('id');
        var model = checkbox.data('model');
        var field = checkbox.data('field') || 'is_active';
        var toggleUrl = $('meta[name="admin-toggle-active-url"]').attr('content') || '/admin/toggle-active';

        $.ajax({
            url: toggleUrl,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ model: model, id: id, field: field, _token: csrfToken }),
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            success: function (data) {
                if (!data.success && data.success !== undefined) {
                    checkbox.prop('checked', !checkbox.prop('checked'));
                }
                if (data.success && typeof toastrr !== 'undefined') {
                    toastrr(globalAjaxSuccessLabel, data.message || 'Status updated', 'success');
                }
                reloadAllDataTables();
            },
            error: function () {
                checkbox.prop('checked', !checkbox.prop('checked'));
                if (typeof toastrr !== 'undefined') {
                    toastrr(globalAjaxErrorLabel, globalAjaxErrorMessage, 'error');
                }
            }
        });
    });

})(jQuery);
