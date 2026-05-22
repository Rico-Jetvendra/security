
<textarea
    class="form-control"
    name="{{ $field['name'] }}"
    id="{{ $field['name'] }}"
    rows="3"
    style="resize:none"
    placeholder="{{ $field['label'] }}"
    @required($field['required'] ?? false)
></textarea>
