<form method="POST" action="{{ route('admin.settings.update', $setting->id) }}">
    @csrf
    @method('PUT')

<div class="row">

    {{-- APP NAME --}}
    <div class="col-md-12">
        <div class="form-group">
            <label for="app_name">{{ __('App Name') }}</label>
            <input type="text"
                   name="app_name"
                   id="app_name"
                   value="{{ old('app_name', $setting->app_name ?? '') }}"
                   class="form-control"
                   placeholder="{{ __('Enter new App Name') }}"
                   required>
        </div>
    </div>

    {{-- GOOGLE ADMOB --}}
    <div class="col-md-6">
        <div class="form-group">
            <label class="custom-switch pl-0">
                <span class="custom-switch-description mr-3">Enable Google Admob ID</span>
                <input type="checkbox"
                       name="enable_google_admob_id"
                       value="1"
                       class="custom-switch-input"
                       {{ old('enable_google_admob_id', $setting->enable_google_admob_id ?? 0) ? 'checked' : '' }}>
                <span class="custom-switch-indicator"></span>
            </label>
        </div>

        @php
            $admobFields = [
                'google_admob_app_open_id' => 'App Open',
                'google_admob_banner_id' => 'Banner ID',
                'google_admob_interstitial_id' => 'Interstitial ID',
                'google_admob_native_id' => 'Native ID',
                'google_admob_rewarded_video_id' => 'Rewarded Video ID',
            ];
        @endphp

        @foreach($admobFields as $name => $label)
        <div class="form-group">
            <label>{{ $label }}</label>
            <input type="text"
                   name="{{ $name }}"
                   value="{{ old($name, $setting->$name ?? '') }}"
                   class="form-control">
        </div>
        @endforeach
    </div>

    {{-- RIGHT SIDE SETTINGS --}}
    <div class="col-md-6">

        <div class="form-group">
            <label class="custom-switch pl-0">
                <span class="custom-switch-description mr-3">App Update</span>
                <input type="checkbox"
                       name="app_update_id"
                       value="1"
                       class="custom-switch-input"
                       {{ old('app_update_id', $setting->app_update_id ?? 0) ? 'checked' : '' }}>
                <span class="custom-switch-indicator"></span>
            </label>
        </div>

        @php
            $rightFields = [
                'google_admob_click_count_id' => 'Interstitial Click Count',
                'app_under_maintenance_id' => 'App In Under Maintenance',
                'google_back_adon_off_id' => 'Back Ads On',
                'app_exit_screen_onoff_id' => 'Exit Screen',
            ];
        @endphp

        @foreach($rightFields as $name => $label)
        <div class="form-group">
            <label>{{ $label }}</label>
            <input type="text"
                   name="{{ $name }}"
                   value="{{ old($name, $setting->$name ?? '') }}"
                   class="form-control">
        </div>
        @endforeach
    </div>

    {{-- QUEREKA --}}
    <div class="col-md-6">
        <div class="form-group">
            <label class="custom-switch pl-0">
                <span class="custom-switch-description mr-3">Enable Quereka Link</span>
                <input type="checkbox"
                       name="enable_quereka_link"
                       value="1"
                       class="custom-switch-input"
                       {{ old('enable_quereka_link', $setting->enable_quereka_link ?? 0) ? 'checked' : '' }}>
                <span class="custom-switch-indicator"></span>
            </label>
        </div>

        <div class="form-group">
            <label>Quereka Link</label>
            <input type="text"
                   name="quereka_link"
                   value="{{ old('quereka_link', $setting->quereka_link ?? '') }}"
                   class="form-control">
        </div>
    </div>

    {{-- STARTAPP --}}
    <div class="col-md-6">
        <div class="form-group">
            <label class="custom-switch pl-0">
                <span class="custom-switch-description mr-3">Enable StartApp ID</span>
                <input type="checkbox"
                       name="enable_startapp_id"
                       value="1"
                       class="custom-switch-input"
                       {{ old('enable_startapp_id', $setting->enable_startapp_id ?? 0) ? 'checked' : '' }}>
                <span class="custom-switch-indicator"></span>
            </label>
        </div>

        <div class="form-group">
            <label>StartApp ID</label>
            <input type="text"
                   name="startapp_id"
                   value="{{ old('startapp_id', $setting->startapp_id ?? '') }}"
                   class="form-control">
        </div>
    </div>

    {{-- APPNEXT --}}
    <div class="col-md-6">
        <div class="form-group">
            <label class="custom-switch pl-0">
                <span class="custom-switch-description mr-3">Enable AppNext ID</span>
                <input type="checkbox"
                       name="enable_appnext_id"
                       value="1"
                       class="custom-switch-input"
                       {{ old('enable_appnext_id', $setting->enable_appnext_id ?? 0) ? 'checked' : '' }}>
                <span class="custom-switch-indicator"></span>
            </label>
        </div>

        @foreach(['appnext_id_1','appnext_id_2','appnext_id_3'] as $field)
        <div class="form-group">
            <label>{{ ucfirst(str_replace('_', ' ', $field)) }}</label>
            <input type="text"
                   name="{{ $field }}"
                   value="{{ old($field, $setting->$field ?? '') }}"
                   class="form-control">
        </div>
        @endforeach
    </div>

    {{-- APPLOVIN --}}
    <div class="col-md-6">
        <div class="form-group">
            <label class="custom-switch pl-0">
                <span class="custom-switch-description mr-3">Enable AppLovin ID</span>
                <input type="checkbox"
                       name="enable_applovin_id"
                       value="1"
                       class="custom-switch-input"
                       {{ old('enable_applovin_id', $setting->enable_applovin_id ?? 0) ? 'checked' : '' }}>
                <span class="custom-switch-indicator"></span>
            </label>
        </div>

        @foreach(['applovin_id_1','applovin_id_2','applovin_id_3'] as $field)
        <div class="form-group">
            <label>{{ ucfirst(str_replace('_', ' ', $field)) }}</label>
            <input type="text"
                   name="{{ $field }}"
                   value="{{ old($field, $setting->$field ?? '') }}"
                   class="form-control">
        </div>
        @endforeach
    </div>

    {{-- AD COLONY --}}
    <div class="col-md-6">
        <div class="form-group">
            <label class="custom-switch pl-0">
                <span class="custom-switch-description mr-3">Enable AD Colony ID</span>
                <input type="checkbox"
                       name="enable_ad_colony_id"
                       value="1"
                       class="custom-switch-input"
                       {{ old('enable_ad_colony_id', $setting->enable_ad_colony_id ?? 0) ? 'checked' : '' }}>
                <span class="custom-switch-indicator"></span>
            </label>
        </div>

        @foreach(['ad_colony_id_1','ad_colony_id_2','ad_colony_id_3'] as $field)
        <div class="form-group">
            <label>{{ ucfirst(str_replace('_', ' ', $field)) }}</label>
            <input type="text"
                   name="{{ $field }}"
                   value="{{ old($field, $setting->$field ?? '') }}"
                   class="form-control">
        </div>
        @endforeach
    </div>

    {{-- CHARTBOOST --}}
    <div class="col-md-6">
        <div class="form-group">
            <label class="custom-switch pl-0">
                <span class="custom-switch-description mr-3">Enable ChartBoost ID</span>
                <input type="checkbox"
                       name="enable_chartboost_id"
                       value="1"
                       class="custom-switch-input"
                       {{ old('enable_chartboost_id', $setting->enable_chartboost_id ?? 0) ? 'checked' : '' }}>
                <span class="custom-switch-indicator"></span>
            </label>
        </div>

        @foreach(['chartboost_id_1','chartboost_id_2','chartboost_id_3'] as $field)
        <div class="form-group">
            <label>{{ ucfirst(str_replace('_', ' ', $field)) }}</label>
            <input type="text"
                   name="{{ $field }}"
                   value="{{ old($field, $setting->$field ?? '') }}"
                   class="form-control">
        </div>
        @endforeach
    </div>

</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">
        {{ __('Cancel') }}
    </button>
    <button type="submit" class="btn btn-primary">
        {{ __('Save') }}
    </button>
</div>

</form>
