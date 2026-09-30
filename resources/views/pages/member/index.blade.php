@include('components.header', ['title' => 'Member'])

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Member</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('web.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Member</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                <x-crud-table
                    :data="$data"
                    :columns="$columns"
                    primaryKey="member_id"
                />
            </div>
        </div>
    </div>
</main>

<x-crud-modal
    title="Member"
    :fields="[
        ['name' => 'teams_name', 'id' => 'teams_id', 'label' => 'Teams', 'type' => 'select', 'required' => true, 'options' => $selects['teams']],
        ['name' => 'member_name', 'id' => 'user_id', 'label' => 'Member', 'type' => 'select', 'required' => true, 'options' => $selects['users']],
        ['name' => 'is_leader', 'id' => 'is_leader', 'label' => 'Is Leader', 'type' => 'checkbox'],
    ]"
    :selects="[
        ['name' => 'teams_name', 'selects' => $selects['teams']],
        ['name' => 'member_name', 'selects' => $selects['users']],
    ]"
/>

@include('components.footer')

<script>
    const columns          = @json($columns);
    const permissions      = @json(session('permission_security'));
    const basePermission   = "{{ permission() }}";

    initCrud({
        routes: {
            store: "{{ route('web.member.store') }}",
            update: id => "{{ route('web.member.update', ':id') }}".replace(':id', id),
            edit: id => "{{ route('web.member.edit', ':id') }}".replace(':id', id),
            destroy: id => "{{ route('web.member.destroy', ':id') }}".replace(':id', id),
            data: "{{ route('web.member.data') }}"
        },
        fields: {
            'teams_id': 'teams_name',
            'username': 'username',
            'user_id': 'member_name',
            'is_leader': 'checkbox',
        },
        columns: columns,
        permissions: permissions,
        basePermission: basePermission
    });
</script>
