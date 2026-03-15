@extends('layouts.admin')

@section('content')
    <div class="section-header">
        <h1>{{ $viewData['title'] }}</h1>
        <div class="section-header-button">
            <a href="{{ route('admin.payments.export') }}" id="payments-export-link" class="btn btn-outline-success btn-sm mr-2"><i
                    class="fas fa-file-csv mr-1"></i> Export CSV</a>
            <button class="btn btn-info btn-sm" type="button" data-toggle="collapse" data-target="#filterSidebar"
                aria-expanded="false" aria-controls="filterSidebar">
                <i class="fas fa-filter mr-1"></i> Filters
            </button>
        </div>
    </div>

    <div class="section-body">

        <div class="card collapse mb-3" id="filterSidebar">
            <div class="card-body">
                <div class="row align-items-end flex-wrap">
                    <x-datatable.common.filter-select-drop-down id="filter_status" name="filter_status"
                        :options="['success' => 'Success', 'pending' => 'Pending', 'pending_webhook' => 'Pending Webhook', 'failed' => 'Failed']" isCustom="true"
                        isCustomCol="3" haslabel="Payment Status" />

                    <div class="col-6 col-md-3 col-lg-2 mt-2">
                        <label for="filter_start_date" class="form-label mb-1 small font-weight-bold">Start date</label>
                        <input type="date" id="filter_start_date" class="form-control form-control-sm" value="{{ request('filter_start_date', '') }}">
                    </div>
                    <div class="col-6 col-md-3 col-lg-2 mt-2">
                        <label for="filter_end_date" class="form-label mb-1 small font-weight-bold">End date</label>
                        <input type="date" id="filter_end_date" class="form-control form-control-sm" value="{{ request('filter_end_date', '') }}">
                    </div>
                    <div class="col-12 col-md-4 col-lg-3 mt-2" id="reset-filters-container">
                        <div class="form-group mb-0">
                            <label class="form-label mb-2 d-block font-weight-bold">&nbsp;</label>
                            <button class="btn btn-primary btn-sm" type="button" id="reset-filters-btn">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table table-striped table-bordered w-100', 'id' => $viewData['dataTableID'] ?? 'payment-transaction-table']) }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
    <script>
        $(document).ready(function () {
            if ($('.select2').length) {
                $('.select2').select2({ placeholder: "Select an option", allowClear: true });
            }

            $('#payments-export-link').on('click', function (e) {
                var params = [];
                var s = $('#filter_status').val();
                var start = $('#filter_start_date').val();
                var end = $('#filter_end_date').val();
                if (s) params.push('filter_status=' + encodeURIComponent(s));
                if (start) params.push('filter_start_date=' + encodeURIComponent(start));
                if (end) params.push('filter_end_date=' + encodeURIComponent(end));
                if (params.length) {
                    e.preventDefault();
                    window.location.href = $(this).attr('href') + '?' + params.join('&');
                }
            });

            $('#filter_status, #filter_start_date, #filter_end_date').on('change', function () {
                updateTable();
                scheduleCheckFilters();
            });

            $('#reset-filters-btn').click(function () {
                $('#filter_status').val(null).trigger('change');
                $('#filter_start_date').val('');
                $('#filter_end_date').val('');
                updateTable();
                checkFilters();
            });
            checkFilters();
        });

        function scheduleCheckFilters() {
            setTimeout(function () {
                checkFilters();
            }, 0);
        }

        function updateTable() {
            const dataTableID = "{{ $viewData['dataTableID'] ?? 'payment-transaction-table' }}";
            const $dataTable = $("#" + dataTableID);
            var filterStatus = $('#filter_status').val();
            var filterStart = $('#filter_start_date').val();
            var filterEnd = $('#filter_end_date').val();

            var url = window.location.pathname;
            var params = [];

            if (filterStatus != null && filterStatus !== "") {
                params.push("filter_status=" + encodeURIComponent(filterStatus));
            }
            if (filterStart) {
                params.push("filter_start_date=" + encodeURIComponent(filterStart));
            }
            if (filterEnd) {
                params.push("filter_end_date=" + encodeURIComponent(filterEnd));
            }

            if (params.length > 0) {
                url += "?" + params.join("&");
            }

            if ($.fn.DataTable.isDataTable("#" + dataTableID)) {
                var table = $dataTable.DataTable();
                table.ajax.url(url).load(function () {
                    checkFilters();
                });
            }
        }

        function checkFilters() {
            var filterStatus = $('#filter_status').val();
            var filterStart = $('#filter_start_date').val();
            var filterEnd = $('#filter_end_date').val();
            var hasFilter = (filterStatus != null && filterStatus !== "") || (filterStart && filterStart.length > 0) || (filterEnd && filterEnd.length > 0);
            $('#reset-filters-container').toggle(hasFilter);
        }
    </script>
@endpush