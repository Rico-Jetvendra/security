@include('components.header', ['title' => 'Permission'])

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Permission</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('web.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Permission</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                <x-crud-table
                    :data="$data"
                    :columns="$columns"
                    primaryKey="id"
                />
            </div>
        </div>
    </div>
</main>

<x-crud-modal
    title="Permission"
    :fields="[
        ['name' => 'application_name', 'id' => 'application_id', 'label' => 'Application', 'type' => 'select', 'required' => true],
        ['name' => 'name', 'id' => 'name', 'label' => 'Nama Permission', 'type' => 'text', 'required' => true],
        ['name' => 'description', 'id' => 'description', 'label' => 'Description', 'type' => 'textarea', 'required' => false],
        ['name' => 'default', 'id' => 'default', 'label' => 'Default Button', 'type' => 'checkbox'],
    ]"
    :selects="[
        ['name' => 'application_name', 'selects' => $selects['application']],
    ]"
/>

@include('components.footer')

<script>
    const columns          = @json($columns);
    const permissions      = @json(session('permission'));
    const basePermission   = "{{ permission() }}";

    initCrud({
        routes: {
            store: "{{ route('web.permission.store') }}",
            update: id => "{{ route('web.permission.update', ':id') }}".replace(':id', id),
            edit: id => "{{ route('web.permission.edit', ':id') }}".replace(':id', id),
            destroy: id => "{{ route('web.permission.destroy', ':id') }}".replace(':id', id),
            data: "{{ route('web.permission.data') }}"
        },
        fields: {
            'application_id': 'application_name',
            'name': 'name',
            'description': 'description'
        },
        columns: columns,
        permissions: permissions,
        basePermission: basePermission
    });
</script>
