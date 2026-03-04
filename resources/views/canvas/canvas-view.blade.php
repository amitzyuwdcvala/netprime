@if(isset($form))
    <style>
        /* VIP-specific rows are hidden by default; toggled via JS when 'User is VIP' is checked */
        .vip-details {
            display: none;
        }
    </style>
    <form id="{{ $form['formID'] }}" action="{{ $form['saveRoute'] }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            @foreach($form['fields'] as $fieldKey => $field)
                @if($field['inputType'] === 'hidden')
                    <input type="hidden" name="{{ $field['name'] }}" value="{{ $field['defaultValue'] ?? '' }}">
                @else
                    <div class="{{ implode(' ', $field['responsive'] ?? ['col-sm-12', 'mb-3']) }}">
                        @if(!empty($field['label']))
                            <label for="{{ $field['name'] }}" class="form-label">
                                {{ $field['label'] }}
                                @if(isset($form['validations']['rules'][$field['name']]['required']) && $form['validations']['rules'][$field['name']]['required'])
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                        @endif

                        @switch($field['inputType'])
                            @case('text')
                            @case('email')
                            @case('number')
                            @case('password')
                            @case('tel')
                            @case('date')
                                <input
                                    type="{{ $field['inputType'] }}"
                                    class="form-control"
                                    id="{{ $field['name'] }}"
                                    name="{{ $field['name'] }}"
                                    value="{{ $field['defaultValue'] ?? '' }}"
                                    placeholder="{{ $field['placeHolder'] ?? '' }}"
                                    {{ isset($field['disabled']) && $field['disabled'] ? 'disabled' : '' }}
                                    {{ isset($field['readonly']) && $field['readonly'] ? 'readonly' : '' }}
                                >
                                @break

                            @case('textarea')
                                <textarea
                                    class="form-control"
                                    id="{{ $field['name'] }}"
                                    name="{{ $field['name'] }}"
                                    rows="{{ $field['rows'] ?? 3 }}"
                                    placeholder="{{ $field['placeHolder'] ?? '' }}"
                                >{{ $field['defaultValue'] ?? '' }}</textarea>
                                @break

                            @case('select')
                                <select
                                    class="form-control"
                                    id="{{ $field['name'] }}"
                                    name="{{ $field['name'] }}"
                                >
                                    <option value="">{{ $field['placeHolder'] ?? 'Select...' }}</option>
                                    @foreach($field['options'] ?? [] as $optionValue => $optionLabel)
                                        <option value="{{ $optionValue }}" {{ ($field['defaultValue'] ?? '') == $optionValue ? 'selected' : '' }}>
                                            {{ $optionLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                @break

                            @case('radio')
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach($field['options'] ?? [] as $optionValue => $optionLabel)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                   name="{{ $field['name'] }}"
                                                   id="{{ $field['name'] }}_{{ $optionValue }}"
                                                   value="{{ $optionValue }}"
                                                   {{ ($field['defaultValue'] ?? '') == $optionValue ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label" for="{{ $field['name'] }}_{{ $optionValue }}">
                                                {{ $optionLabel }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @break

                            @case('checkbox')
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="{{ $field['name'] }}"
                                           id="{{ $field['name'] }}"
                                           value="1"
                                           {{ ($field['defaultValue'] ?? false) ? 'checked' : '' }}
                                           {{ isset($field['disabled']) && $field['disabled'] ? 'disabled' : '' }}
                                    >
                                    <label class="form-check-label" for="{{ $field['name'] }}">
                                        {{ $field['checkboxLabel'] ?? $field['label'] ?? '' }}
                                    </label>
                                </div>
                                @break

                            @case('file_upload')
                                <input
                                    type="file"
                                    class="form-control"
                                    id="{{ $field['name'] }}"
                                    name="{{ $field['name'] }}"
                                    accept="{{ $field['accept'] ?? '*' }}"
                                >
                                @if(!empty($field['defaultValue']))
                                    <div class="mt-2">
                                        <small class="text-muted">Current: {{ basename($field['defaultValue']) }}</small>
                                    </div>
                                @endif
                                @break

                        @endswitch
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Save
            </button>
            <button type="button" class="btn btn-secondary canvas-close-btn" data-dismiss="modal">
                <i class="fas fa-times mr-1"></i> Cancel
            </button>
        </div>
    </form>

    @if(!empty($form['validations']))
    <script>
        $(document).ready(function() {
            $('#{{ $form['formID'] }}').validate({
                rules: @json($form['validations']['rules'] ?? []),
                messages: @json($form['validations']['messages'] ?? []),
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('[class*="col-"]').append(error);
                },
                highlight: function(element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid');
                }
            });
        });
    </script>
    @endif
@endif
