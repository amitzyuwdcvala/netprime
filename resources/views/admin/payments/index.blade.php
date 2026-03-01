@extends('layouts.admin')

@section('content')
    <div class="section-header">
        <h1>{{ $viewData['title'] }}</h1>
        <div class="section-header-button">
            <a href="{{ route('admin.payments.export') }}" class="btn btn-outline-success btn-sm mr-2"><i
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
                <div class="row align-items-center">
                    <x-datatable.common.filter-select-drop-down id="filter_status" name="filter_status"
                        :options="['Success' => 'Success', 'Pending' => 'Pending', 'Failed' => 'Failed']" isCustom="true"
                        isCustomCol="4" haslabel="Payment Status" />

                    <div class="col-md-4 col-sm-6 col-12 mt-2" id="reset-filters-container" style="display: none;">
                        <div class="form-group mb-0">
                            <label class="form-label mb-2 d-block font-weight-bold">&nbsp;</label>
                            <button class="btn btn-primary" type="button" id="reset-filters-btn">
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

            $('#filter_status').change(function () {
                updateTable();
                checkFilters();
            });

            $('#reset-filters-btn').click(function () {
                $('#filter_status').val(null).trigger('change');
                updateTable();
                checkFilters();
            });
            checkFilters();
        });

        function updateTable() {
            const dataTableID = "{{ $viewData['dataTableID'] ?? 'payment-transaction-table' }}";
            const $dataTable = $("#" + dataTableID);
            var filterStatus = $('#filter_status').val();

            var url = window.location.pathname;
            var params = [];

            if (filterStatus != null && filterStatus !== "") {
                params.push("filter_status=" + filterStatus);
            }

            if (params.length > 0) {
                url += "?" + params.join("&");
            }

            if ($.fn.DataTable.isDataTable("#" + dataTableID)) {
                var table = $dataTable.DataTable();
                table.ajax.url(url).load();
            }
        }

        function checkFilters() {
            var filterStatus = $('#filter_status').val();
            if (filterStatus != null && filterStatus !== "") {
                $('#reset-filters-container').show();
            } else {
                $('#reset-filters-container').hide();
            }
        }
    </script>
@endpush