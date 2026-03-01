<div class="col-md-{{ $isCustomCol ?? 4 }} col-sm-6 col-12 mt-2">
    <div class="form-group mb-0">
        @if (isset($haslabel))
            <label for="" class="form-label mb-2 d-block font-weight-bold">{{ $haslabel }}</label>
        @endif
        <select id="{{ $id }}" name="{{ $id }}" class="select2 form-control w-100" style="width: 100%;"
            data-allow-clear="true">
            <option value="">Select {{ $name }}</option>
            @if ($isCustom == "false")
                @foreach ($options as $item)
                    <option value="{{ $item->id }}">
                        {{  ucwords($item->name) }}
                    </option>
                @endforeach
            @else
                @foreach ($options as $key => $item)
                    <option value="{{ $key }}">
                        {{ ucwords($item) }}
                    </option>
                @endforeach
            @endif

        </select>
    </div>
</div>