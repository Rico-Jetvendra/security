@include('components.header', ['title' => 'Teams'])

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Teams</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('web.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Teams</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                <x-crud-table
                    :data="$data"
                    :columns="$columns"
                    primaryKey="teams_id"
                />
            </div>
        </div>
    </div>
</main>

<x-crud-modal
    title="Teams"
    :fields="[
        ['name' => 'teams_name', 'id' => 'teams_name', 'label' => 'Name', 'type' => 'text', 'required' => true],
        ['name' => 'parent_name', 'id' => 'parent_id', 'label' => 'Parent', 'type' => 'select', 'required' => false, 'options' => $selects['parents']],
        ['name' => 'teams_description', 'id' => 'teams_description', 'label' => 'Description', 'type' => 'textarea', 'required' => true],
    ]"
    :selects="[
        ['name' => 'parent_name', 'selects' => $selects['parents']],
    ]"
/>

@include('components.footer')

<script>
    const columns          = @json($columns);
    const permissions      = @json(session('permission_security'));
    const basePermission   = "{{ permission() }}";

    initCrud({
        routes: {
            store: "{{ route('web.teams.store') }}",
            update: id => "{{ route('web.teams.update', ':id') }}".replace(':id', id),
            edit: id => "{{ route('web.teams.edit', ':id') }}".replace(':id', id),
            destroy: id => "{{ route('web.teams.destroy', ':id') }}".replace(':id', id),
            data: "{{ route('web.teams.data') }}"
        },
        fields: {
            'teams_name': 'teams_name',
            'parent_id': 'parent_name',
            'teams_description': 'teams_description',
        },
        columns: columns,
        permissions: permissions,
        basePermission: basePermission
    });
</script>
