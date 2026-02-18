@extends('layouts.admin')

@section('content')
    <div class="section-header">
        <h1>{{ $viewData['title'] }}</h1>
        <div class="section-header-button">
            <button class="btn btn-primary add-record-btn">
                <i class="fas fa-plus mr-1"></i> Add New
            </button>
        </div>
    </div>

    <div class="section-body"
         data-manage-route="{{ $viewData['manageRoute'] }}"
         data-delete-route="{{ $viewData['deleteRoute'] }}">

        <div class="card">
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table table-striped table-bordered w-100', 'id' => $viewData['dataTableID']]) }}
            </div>
        </div>
    </div>

    {{-- Offcanvas / Modal for Create/Edit --}}
    <div id="{{ $viewData['canvasId'] }}" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $viewData['canvasHeading'] }}</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    {{-- Dynamic form loaded here via AJAX --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
@endpush
