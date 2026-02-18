@extends('layouts.admin')

@section('content')
    <div class="section-header">
        <h6 class="page-title">{{ __('ADs ID Settings') }}</h6>
        <div class="section-header-breadcrumb">
            <a href="#" class="btn btn-sm btn-icon icon-left btn-primary btn-radius" data-ajax-popup="true" data-size="lg"
                data-title="{{ __('Set Up IDs') }}" data-url="{{ route('admin.settings.create') }}">
                <span class="btn-inner--icon"><i class="fa fa-plus"></i></span>
                <span class="btn-inner--text">{{ __('Set Up IDs') }}</span>
            </a>
        </div>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped custom-datatable">
                                <thead>
                                    <tr>
                                        <th style="width: 8%;">
                                            <div class="row">
                                                <div class="col-6 d-none">
                                                    <div class="custom-checkbox custom-control">
                                                        <input type="checkbox" data-checkboxes="mygroup"
                                                            data-checkbox-role="dad" class="custom-control-input"
                                                            id="checkbox-all">
                                                        <label for="checkbox-all" class="custom-control-label"></label>
                                                    </div>
                                                </div>
                                                <div class="col-12" style="margin-left: 11px;">
                                                    <a href="#" class="btn btn-sm btn-icon btn-danger btn-delete-all"
                                                        data-url="{{ route('admin.settings.delete-selected') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </th>
                                        <th>#</th>
                                        <th>{{ __('App Name') }}</th>
                                        <th>{{ __('Google Admob ID') }}</th>
                                        <th>{{ __('App Update') }}</th>
                                        <th>{{ __('Quereka Link') }}</th>
                                        <th>{{ __('StartApp ID') }}</th>
                                        <th>{{ __('AppNext ID') }}</th>
                                        <th>{{ __('Applovin ID') }}</th>
                                        <th>{{ __('AD Colony ID') }}</th>
                                        <th>{{ __('ChartBoost ID') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($settings as $key => $setting)
                                        <tr id="checkbox_checked_id{{ $setting->id }}">
                                            <td class="p-0 text-center">
                                                <div class="custom-checkbox custom-control">
                                                    <input type="checkbox" class="custom-control-input btn-delete-check"
                                                        id="setting-{{ $setting->id }}" value="{{ $setting->id }}">
                                                    <label for="setting-{{ $setting->id }}"
                                                        class="custom-control-label"></label>
                                                </div>
                                            </td>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $setting->app_name }}</td>
                                            <td>{{ $setting->enable_google_admob_id ? 'ON' : 'OFF' }}</td>
                                            <td>{{ $setting->app_update_id ? 'ON' : 'OFF' }}</td>
                                            <td>{{ $setting->enable_quereka_link ? 'ON' : 'OFF' }}</td>
                                            <td>{{ $setting->enable_startapp_id ? 'ON' : 'OFF' }}</td>
                                            <td>{{ $setting->enable_appnext_id ? 'ON' : 'OFF' }}</td>
                                            <td>{{ $setting->enable_applovin_id ? 'ON' : 'OFF' }}</td>
                                            <td>{{ $setting->enable_ad_colony_id ? 'ON' : 'OFF' }}</td>
                                            <td>{{ $setting->enable_chartboost_id ? 'ON' : 'OFF' }}</td>
                                            <td class="pull-right">
                                                <a href="#" class="btn btn-sm btn-primary btn-actions"
                                                    data-ajax-popup="true" data-title="{{ __('Edit Setting') }}"
                                                    data-size="lg" data-url="{{ route('admin.settings.edit', $setting->id) }}"><i
                                                        class="fa fa-pencil-alt"></i><span>{{ __('Edit') }}</span></a>
                                                <a href="#" class="btn btn-sm btn-danger btn-actions"
                                                    data-confirm="{{ __('Are You Sure?') }}|{{ __('This action can not be undone. Do you want to continue?') }}"
                                                    data-confirm-yes="document.getElementById('delete-form-{{ $setting->id }}').submit();"><i
                                                        class="fa fa-trash"></i><span>{{ __('Delete') }}</span></a>
                                                <form method="POST" action="{{ route('admin.settings.destroy', $setting->id) }}"
                                                    id="delete-form-{{ $setting->id }}" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="text-center">{{ __('No data available in table') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
@endpush

@push('scripts')
@endpush
