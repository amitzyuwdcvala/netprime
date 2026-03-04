@extends('layouts.admin')

@section('content')
    <div class="section-header">
        <h1>{{ $viewData['title'] }}</h1>
        <div class="section-header-button">
            <a href="{{ route('admin.users.export') }}" class="btn btn-outline-success btn-sm mr-2"><i
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
                    <x-datatable.common.filter-select-drop-down id="is_vip_filter" name="is_vip_filter" :options="['1' => 'Yes', '0' => 'No']" isCustom="true" isCustomCol="4" haslabel="VIP Status" />

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
                {{ $dataTable->table(['class' => 'table table-striped table-bordered w-100', 'id' => $viewData['dataTableID'] ?? 'user-table']) }}
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

            $('#is_vip_filter').change(function () {
                updateTable();
                checkFilters();
            });

            $('#reset-filters-btn').click(function () {
                $('#is_vip_filter').val(null).trigger('change');
                updateTable();
                checkFilters();
            });

            // VIP details toggle in canvas modal (user form)
            $(document).on('change', '#is_vip', function () {
                toggleVipDetails();
            });

            // When modal opens, ensure correct visibility based on initial state
            $(document).on('shown.bs.modal', '#{{ $viewData['canvasId'] }}', function () {
                toggleVipDetails();
            });

            checkFilters();
        });

        function updateTable() {
            const dataTableID = "{{ $viewData['dataTableID'] ?? 'user-table' }}";
            const $dataTable = $("#" + dataTableID);
            var isVip = $('#is_vip_filter').val();

            var url = window.location.pathname;
            var params = [];

            if (isVip != null && isVip !== "") {
                params.push("is_vip=" + isVip);
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
            var isVip = $('#is_vip_filter').val();
            if (isVip != null && isVip !== "") {
                $('#reset-filters-container').show();
            } else {
                $('#reset-filters-container').hide();
            }
        }

        function toggleVipDetails() {
            var $vipCheckbox = $('#is_vip');
            if (!$vipCheckbox.length) {
                return;
            }
            var isVipChecked = $vipCheckbox.is(':checked');

            // VIP details section is rendered with .vip-details class
            var $vipDetails = $('.vip-details');

            if (isVipChecked) {
                $vipDetails.show();
            } else {
                $vipDetails.hide();
                // Clear values when hiding, but keep disabled values (locked for real paid users)
                $vipDetails.find('select, input').each(function () {
                    if (!$(this).prop('disabled')) {
                        $(this).val('');
                    }
                });
            }
        }
    </script>
@endpush