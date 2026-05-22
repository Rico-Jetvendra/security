@include('components.header', ['title' => 'Activity Logs'])

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Activity Logs</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('web.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Activity Logs</li>
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

<div class="modal fade" id="viewModal" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Activity Detail
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- USER INFO -->
                <div class="card mb-3">
                    <div class="card-header fw-bold">
                        User Information
                    </div>

                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-3 text-muted">User ID</div>
                            <div class="col-md-9" id="modal_user_id"></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-3 text-muted">Username</div>
                            <div class="col-md-9" id="modal_username"></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-3 text-muted">IP Address</div>
                            <div class="col-md-9">
                                <code id="modal_ip"></code>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACTIVITY -->
                <div class="card mb-3">
                    <div class="card-header fw-bold">
                        Activity
                    </div>

                    <div class="card-body">

                        <div class="row mb-2">
                            <div class="col-md-3 text-muted">Module</div>
                            <div class="col-md-9">
                                <span class="badge bg-primary" id="modal_module"></span>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-3 text-muted">Action</div>
                            <div class="col-md-9">
                                <span class="badge bg-success" id="modal_action"></span>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-3 text-muted">Subject Type</div>
                            <div class="col-md-9">
                                <span class="badge bg-warning" id="modal_subject_type"></span>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-3 text-muted">Subject ID</div>
                            <div class="col-md-9">
                                <span class="badge bg-danger" id="modal_subject_id"></span>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-3 text-muted">Description</div>
                            <div class="col-md-9" id="modal_description"></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-3 text-muted">Created At</div>
                            <div class="col-md-9" id="modal_created_at"></div>
                        </div>

                    </div>
                </div>

                <!-- TECHNICAL -->
                <div class="card mb-3">
                    <div class="card-header fw-bold">
                        Technical Information
                    </div>

                    <div class="card-body">

                        <div class="mb-3">
                            <label class="form-label text-muted">
                                User Agent
                            </label>

                            <div class="bg-light border rounded p-2 small" id="modal_user_agent" style="word-break: break-word;">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- OLD VALUES -->
                <div class="card mb-3 d-none" id="oldValuesCard">
                    <div class="card-header fw-bold">
                        Old Values
                    </div>

                    <div class="card-body">
                        <pre class="bg-dark text-light p-3 rounded small" id="modal_old_values"></pre>
                    </div>
                </div>

                <!-- NEW VALUES -->
                <div class="card d-none" id="newValuesCard">
                    <div class="card-header fw-bold">
                        New Values
                    </div>

                    <div class="card-body">
                        <pre class="bg-dark text-light p-3 rounded small" id="modal_new_values"></pre>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


@include('components.footer')

<script>
    const columns          = @json($columns);
    const permissions      = @json(session('permission'));
    const basePermission   = "{{ permission() }}";

    const tableColumns  = [
        { data: 'DT_RowIndex', orderable: false, searchable: false },

        ...columns.map(col => ({
            data: col.field,
            orderable: col.orderable ?? true,
            searchable: col.searchable ?? true
        })),
    ];

    $.fn.DataTable.ext.pager.numbers_length = 5;
    const table = $('#dataTable').DataTable({
        responsive: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: "{{ route('web.activity-logs.data') }}",
        pagingType: "simple_numbers",
        columns: tableColumns
    });

    $(document).on('click', '.clickable-log', function () {
        const row = $(this).data('row');

        $('#modal_username').text(row.username ?? '-');
        $('#modal_user_id').text(row.user_id ?? '-');
        $('#modal_ip').text(row.ip_address ?? '-');

        $('#modal_module').text(row.module ?? '-');
        $('#modal_action').text(row.action ?? '-');
        $('#modal_subject_type').text(row.subject_type ?? '-');
        $('#modal_subject_id').text(row.subject_id ?? '-');
        $('#modal_description').text(row.description ?? '-');
        $('#modal_created_at').text(new Date(row.created_at) ?? '-');

        $('#modal_user_agent').text(row.user_agent ?? '-');

        // OLD VALUES
        if (row.old_values) {
            $('#oldValuesCard').removeClass('d-none');

            $('#modal_old_values').text(
                JSON.stringify(row.old_values, null, 2)
            );
        } else {
            $('#oldValuesCard').addClass('d-none');
        }

        // NEW VALUES
        if (row.new_values) {
            $('#newValuesCard').removeClass('d-none');

            $('#modal_new_values').text(
                JSON.stringify(row.new_values, null, 2)
            );
        } else {
            $('#newValuesCard').addClass('d-none');
        }

        $('#viewModal').modal('show');
    });
</script>
