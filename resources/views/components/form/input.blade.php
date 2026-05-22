
<input
    type="{{ $field['type'] }}"
    class="form-control"
    name="{{ $field['name'] }}"
    id="{{ $field['name'] }}"
    placeholder="{{ $field['label'] }}"
    @required($field['required'] ?? false)
>
