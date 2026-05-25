@include('components.header', ['title' => 'User'])

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">User</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('web.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">User</li>
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
    title="User"
    :fields="[
        ['name' => 'application_name', 'id' => 'application_id', 'label' => 'Application', 'type' => 'select', 'required' => true],
        ['name' => 'username', 'id' => 'username', 'label' => 'Username', 'type' => 'text', 'required' => true],
        ['name' => 'password', 'id' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true],
        ['name' => 'password_confirmation', 'id' => 'password_confirmation', 'label' => 'Password Confirmation', 'type' => 'password', 'required' => true],
        ['name' => 'email', 'id' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
        ['name' => 'sales_name', 'id' => 'kode_sales', 'label' => 'Kode Sales', 'type' => 'select', 'required' => false],
    ]"
    :selects="[
        ['name' => 'application_name', 'selects' => $selects['application']],
        ['name' => 'sales_name', 'selects' => $selects['kode_sales']],
    ]"
/>

@include('components.footer')

<script>
    const columns          = @json($columns);
    const permissions      = @json(session('permission_security'));
    const basePermission   = "{{ permission() }}";

    initCrud({
        routes: {
            store: "{{ route('web.user.store') }}",
            update: id => "{{ route('web.user.update', ':id') }}".replace(':id', id),
            edit: id => "{{ route('web.user.edit', ':id') }}".replace(':id', id),
            destroy: id => "{{ route('web.user.destroy', ':id') }}".replace(':id', id),
            data: "{{ route('web.user.data') }}"
        },
        fields: {
            'application_id': 'application_name',
            'kode_sales': 'sales_name',
            'username': 'username',
            'email': 'email',
            'password': 'password',
            'password_confirmation': 'password_confirmation',
        },
        columns: columns,
        permissions: permissions,
        basePermission: basePermission
    });
</script>
