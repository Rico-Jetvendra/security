<div class="card">
    <div class="card-header">
        @if(in_array(permission('add'), session('permission', [])))
            <button class="btn btn-primary btn-create">
                <i class="bi bi-plus"></i> Add
            </button>
        @endif
    </div>

    <div class="card-body">
        <table id="dataTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th width="60">No</th>
                    @foreach ($columns as $col)
                        <th>{{ $col['label'] }}</th>
                    @endforeach

                    @if(
                        in_array(permission('edit'), session('permission', []))
                        ||
                        in_array(permission('delete'), session('permission', []))
                    )
                        <th width="120">Aksi</th>
                    @endif
                </tr>
            </thead>

            <tbody>
            </tbody>
        </table>
    </div>
</div>
