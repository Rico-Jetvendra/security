@include('components.header', ['title' => 'Application'])

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Application</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('web.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Application</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                <x-crud-table
                    :data="$data"
                    :columns="$columns"
                    primaryKey="application_id"
                />
            </div>
        </div>
    </div>
</main>

<x-crud-modal
    title="Application"
    :fields="[
        ['name' => 'application_name', 'id' => 'application_name', 'label' => 'Application', 'type' => 'text', 'required' => true],
        ['name' => 'application_type_name', 'id' => 'application_type', 'label' => 'Type', 'type' => 'select', 'required' => true, 'options' => $selects['app_type']],
    ]"
    :selects="[
        ['name' => 'application_type_name', 'selects' => $selects['app_type']],
    ]"
/>

@include('components.footer')

<script>
    const columns          = @json($columns);
    const permissions      = @json(session('permission_security'));
    const basePermission   = "{{ permission() }}";

    initCrud({
        routes: {
            store: "{{ route('web.application.store') }}",
            update: id => "{{ route('web.application.update', ':id') }}".replace(':id', id),
            edit: id => "{{ route('web.application.edit', ':id') }}".replace(':id', id),
            destroy: id => "{{ route('web.application.destroy', ':id') }}".replace(':id', id),
            data: "{{ route('web.application.data') }}"
        },
        fields: {
            'application_name': 'application_name',
            'application_type': 'application_type_name',
        },
        columns: columns,
        permissions: permissions,
        basePermission: basePermission
    });
</script>
