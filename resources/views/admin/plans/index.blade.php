@extends('layouts.admin')

@section('content')
    <div class="section-header">
        <h1>{{ $viewData['title'] }}</h1>
        <div class="section-header-button">
            <a href="{{ route('admin.plans.export') }}" class="btn btn-outline-success btn-sm mr-2"><i
                    class="fas fa-file-csv mr-1"></i> Export CSV</a>
            <button class="btn btn-info btn-sm mr-2" type="button" data-toggle="collapse" data-target="#filterSidebar"
                aria-expanded="false" aria-controls="filterSidebar">
                <i class="fas fa-filter mr-1"></i> Filters
            </button>
            <button class="btn btn-primary add-record-btn">
                <i class="fas fa-plus mr-1"></i> Add New
            </button>
        </div>
    </div>

    <div class="section-body" data-manage-route="{{ $viewData['manageRoute'] }}"
        data-delete-route="{{ $viewData['deleteRoute'] }}">

        <div class="card collapse mb-3" id="filterSidebar">
            <div class="card-body">
                <div class="row align-items-center">
                    <x-datatable.common.filter-select-drop-down id="filter_active" name="filter_active" :options="['1' => 'Active', '0' => 'Inactive']" isCustom="true" isCustomCol="4" haslabel="Status" />
                    <x-datatable.common.filter-select-drop-down id="filter_popular" name="filter_popular" :options="['1' => 'Popular', '0' => 'Normal']" isCustom="true" isCustomCol="4" haslabel="Popularity" />

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
                {{ $dataTable->table(['class' => 'table table-striped table-bordered w-100', 'id' => $viewData['dataTableID'] ?? 'subscription-plan-table']) }}
            </div>
        </div>
    </div>

    <div id="{{ $viewData['canvasId'] }}" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $viewData['canvasHeading'] }}</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                </div>
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

            $('#filter_active, #filter_popular').change(function () {
                updateTable();
                checkFilters();
            });

            $('#reset-filters-btn').click(function () {
                $('#filter_active').val(null).trigger('change');
                $('#filter_popular').val(null).trigger('change');
                updateTable();
                checkFilters();
            });
            checkFilters();
        });

        function updateTable() {
            const dataTableID = "{{ $viewData['dataTableID'] ?? 'subscription-plan-table' }}";
            const $dataTable = $("#" + dataTableID);
            var filterActive = $('#filter_active').val();
            var filterPopular = $('#filter_popular').val();

            var url = window.location.pathname;
            var params = [];

            if (filterActive != null && filterActive !== "") {
                params.push("filter_active=" + filterActive);
            }
            if (filterPopular != null && filterPopular !== "") {
                params.push("filter_popular=" + filterPopular);
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
            var filterActive = $('#filter_active').val();
            var filterPopular = $('#filter_popular').val();
            if ((filterActive != null && filterActive !== "") || (filterPopular != null && filterPopular !== "")) {
                $('#reset-filters-container').show();
            } else {
                $('#reset-filters-container').hide();
            }
        }
    </script>
@endpush