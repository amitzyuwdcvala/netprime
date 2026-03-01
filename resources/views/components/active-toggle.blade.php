@props(['model', 'field' => 'is_active', 'asSwitch' => false])

@if($asSwitch)
    @php $switchId = 'switch-' . str_replace(['/', '\\'], '-', $model->getKey()); @endphp
    <div class="custom-control custom-switch d-inline-block">
        <input type="checkbox" class="custom-control-input is_active_status" id="{{ $switchId }}"
               data-id="{{ $model->getKey() }}" data-model="{{ get_class($model) }}" data-field="{{ $field }}"
               {{ $model->$field ? 'checked' : '' }} title="Toggle active" />
        <label class="custom-control-label" for="{{ $switchId }}"></label>
    </div>
@else
    <input type="checkbox" class="form-check-input is_active_status" data-id="{{ $model->getKey() }}" data-model="{{ get_class($model) }}" data-field="{{ $field }}" {{ $model->$field ? 'checked' : '' }} title="Toggle active" />
@endif
