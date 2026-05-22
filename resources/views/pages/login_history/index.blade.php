@include('components.header', ['title' => 'Login History'])

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Login History</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('web.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Login History</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <table id="dataTable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th width="60">No</th>
                                    @foreach ($columns as $col)
                                        <th>{{ $col['label'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@include('components.footer')

<script>
    const columns          = @json($columns);
    const permissions      = @json(session('permission'));
    const basePermission   = "{{ permission() }}";

    initCrud({
        routes: {
            data: "{{ route('web.login-history.data') }}"
        },
        fields: {
            'application_id': 'application_name',
            'name': 'name',
        },
        columns: columns,
        permissions: permissions,
        basePermission: basePermission
    });
</script>
