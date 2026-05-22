@include('components.header', ['title' => 'User Role'])

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">User Role</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('web.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">User Role</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                <x-crud-table
                    :data="$data"
                    :columns="$columns"
                    primaryKey="user_roles_id"
                />
            </div>
        </div>
    </div>
</main>

<x-crud-modal
    title="User Role"
    :fields="[
        ['name' => 'user_name', 'id' => 'user_id', 'label' => 'User', 'type' => 'select', 'required' => true],
        ['name' => 'role_name', 'id' => 'role_id', 'label' => 'Role', 'type' => 'select', 'required' => true],
    ]"
    :selects="[
        ['name' => 'user_name', 'selects' => $selects['user']],
        ['name' => 'role_name', 'selects' => $selects['role']],
    ]"
/>

@include('components.footer')

<script>
    const columns          = @json($columns);
    const permissions      = @json(session('permission'));
    const basePermission   = "{{ permission() }}";

    initCrud({
        routes: {
            store: "{{ route('web.user-role.store') }}",
            update: id => "{{ route('web.user-role.update', ':id') }}".replace(':id', id),
            edit: id => "{{ route('web.user-role.edit', ':id') }}".replace(':id', id),
            destroy: id => "{{ route('web.user-role.destroy', ':id') }}".replace(':id', id),
            data: "{{ route('web.user-role.data') }}"
        },
        fields: {
            'role_id': 'role_name',
            'user_id': 'user_name',
        },
        columns: columns,
        permissions: permissions,
        basePermission: basePermission
    });
</script>
