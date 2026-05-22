
<input type="text"
    class="form-control datepicker"
    name="{{ $field['name'] }}"
    id="{{ $field['name'] }}"
    placeholder="dd-mm-yyyy"
    @required($field['required'] ?? false)
>
