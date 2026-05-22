@switch($field['type'])

    @case('textarea')
        <x-form.textarea :field="$field" />
        @break

    @case('email')
    @case('password')
    @case('text')
        <x-form.input :field="$field" />
        @break

    @case('date')
        <x-form.date :field="$field" />
        @break

    @case('select')
        <x-form.select :field="$field" :selects="$selects" />
        @break

    @case('checkbox')
        <x-form.checkbox :field="$field" />
        @break

@endswitch
