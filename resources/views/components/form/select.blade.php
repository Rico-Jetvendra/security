@php
    $currentSelect = collect($selects)->firstWhere('name', $field['name']);
@endphp
<select
    class="form-select searchable-select"
    name="{{ $field['id'] }}"
    id="{{ $field['id'] }}"
    {{ $field['required'] ? 'required' : '' }}
>
    <option value="">-- Pilih {{ $field['label'] }} --</option>

    @foreach ($currentSelect['selects'] ?? [] as $option)
        <option value="{{ $option->{$field['id']} }}">
            {{ $option->{$field['name']} }}
        </option>
    @endforeach
</select>
