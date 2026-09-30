@include('components.header', ['title' => 'Role'])

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Role</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('web.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Role</li>
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
    title="Role"
    :fields="[
        ['name' => 'application_name', 'id' => 'application_id', 'label' => 'Application', 'type' => 'select', 'required' => true],
        ['name' => 'name', 'id' => 'name', 'label' => 'Nama Role', 'type' => 'text', 'required' => true],
        ['name' => 'dashboard', 'id' => 'dashboard', 'label' => 'Default Dashboard', 'type' => 'text', 'required' => false],
        ['name' => 'description', 'id' => 'description', 'label' => 'Description', 'type' => 'textarea', 'required' => false],
    ]"
    :selects="[
        ['name' => 'application_name', 'selects' => $selects['application']],
    ]"
/>

@include('components.footer')

<script>
    const columns          = @json($columns);
    const permissions      = @json(session('permission_security'));
    const basePermission   = "{{ permission() }}";

    initCrud({
        routes: {
            store: "{{ route('web.role.store') }}",
            update: id => "{{ route('web.role.update', ':id') }}".replace(':id', id),
            edit: id => "{{ route('web.role.edit', ':id') }}".replace(':id', id),
            destroy: id => "{{ route('web.role.destroy', ':id') }}".replace(':id', id),
            data: "{{ route('web.role.data') }}"
        },
        fields: {
            'application_id': 'application_name',
            'name': 'name',
            'dashboard': 'dashboard',
            'description': 'description'
        },
        columns: columns,
        permissions: permissions,
        basePermission: basePermission
    });
</script>
