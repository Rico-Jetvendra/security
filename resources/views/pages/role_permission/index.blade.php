@include('components.header', ['title' => 'Role Permission'])

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Role Permission</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('web.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Role Permission</li>
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

<div class="modal fade" id="crudModal" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form method="POST" id="crudForm">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <span id="modalTitle">Tambah</span> Role Permission
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="mb-3">
                                <label class="form-label" for="application_id">Application</label>
                                <select class="form-control searchable-select" name="application_id" id="application_id" onchange="fillSelect(this)" required>
                                    <option value="">Pilih Application</option>
                                    @foreach ($selects['application'] as $application)
                                        <option value="{{ $application->application_id }}" data-type="{{ $application->application_type }}">{{ $application->application_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="selectRolePermission" style="display: none">
                                <div class="mb-3">
                                    <label class="form-label" for="role_id">Role</label>
                                    <select class="form-control searchable-select" name="role_id" id="role_id" required>
                                        <option value="">Pilih Role</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="permission_id">Permission</label>
                                    <div id="permissionTree"></div>
                                    <div id="permissionUI"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>

            </form>

        </div>
    </div>
</div>


@include('components.footer')

<script>
    const columns           = @json($columns);
    const permissions       = @json(session('permission_security'));
    const modal             = $('#crudModal');
    const form              = $('#crudForm');
    const defaultSearch     = $('#defaultSearch').val();
    const basePermission    = "{{ permission() }}";
    const routes = {
        store: "{{ route('web.role-permission.store') }}",
        update: id => "{{ route('web.role-permission.update', ':id') }}".replace(':id', id),
        edit: id => "{{ route('web.role-permission.edit', ':id') }}".replace(':id', id),
        destroy: id => "{{ route('web.role-permission.destroy', ':id') }}".replace(':id', id),
        data: "{{ route('web.role-permission.data') }}"
    };
    const fields = {
        'application_id': 'application_name',
        'role_id': 'role_name',
        'permission_id': 'permission_name',
    };

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const tableColumns  = [
        { data: 'DT_RowIndex', orderable: false, searchable: false },

        ...columns.map(col => ({
            data: col.field,
            orderable: col.orderable ?? true,
            searchable: col.searchable ?? true
        })),
    ];

    if(permissions.includes(basePermission+'.edit') || permissions.includes(basePermission+'.delete')){
        tableColumns.push({
            data: 'action',
            orderable: false,
            searchable: false
        });
    }

    $.fn.DataTable.ext.pager.numbers_length = 5;

    // Initialize DataTable
    const table = $('#dataTable').DataTable({
        responsive:true,
        autoWidth:false,
        processing: true,
        serverSide: true,
        ajax: routes.data,
        pagingType: "simple_numbers",
        columns: tableColumns,
        search: {
            search: defaultSearch
        }
    });

    // OPEN CREATE
    $('.btn-create').click(() => {
        form.trigger('reset');
        form.attr('action', routes.store);
        $('#formMethod').val('POST');
        $('#modalTitle').text('Tambah');
        modal.modal('show');

        $('#selectRolePermission').css('display', 'none');
    });

    // OPEN EDIT
    $(document).on('click', '.btn-edit', function () {
        const id = $(this).data('id');

        form.attr('action', routes.update(id));
        $('#formMethod').val('PUT');
        $('#modalTitle').text('Edit');

        $.get(routes.edit(id), res => {
            const app_select    = $('#application_id')[0].tomselect;
            const role_select   = $('#role_id')[0].tomselect;

            app_select.setValue(res[0].application_id);

            setTimeout(() => {
                role_select.setValue(res[0].role_id);

                $.each(res, function (item){
                    let permission = res[item].permission_id;

                    if(res[item].application_type == 1){
                        selectedPermissions.push(permission.toString());
                    }else if(res[item].application_type == 2){
                        $(`.permission-checkbox[value="${permission}"]`).prop('checked', true);
                    }
                });
            }, 500);

        });

        modal.modal('show');
    });

    // DELETE
    $(document).on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');

        Swal.fire({
            title: 'You sure?',
            text: "You sure want to delete this " + name + "?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete!',
            cancelButtonText: 'No, cancel!',
        }).then(r => {
            if (!r.isConfirmed){
                return Swal.fire({
                    title: 'Cancelled',
                    text: 'Record not deleted.',
                    icon: 'error'
                });
            };

            destroy(id);
        });
    });

    function destroy(id) {
        $.post(routes.destroy(id), {
            _method: 'DELETE'
        })
        .done(() => {
            Swal.fire({
                title: 'Success!',
                text: 'Record have been deleted.',
                icon: 'success'
            }).then(() => table.draw());
        });
    }

    function fillSelect(application){
        const app   = $(application).find(':selected');
        const type  = app.data('type');
        const route = "{{ route('web.role-permission.select') }}?application_id=" + app.val();

        $.get(route, res => {
            // Role Select
            const roleSelect = $('#role_id')[0].tomselect;

            roleSelect.clear();
            roleSelect.clearOptions();

            $.each(res.roles, function(index, role) {
                roleSelect.addOption({
                    value: role.id,
                    text: role.name
                });
            });

            roleSelect.refreshOptions(false);
            // End Role Select

            // Permission Matrix
            $('#permissionTree').empty();
            $('#permissionUI').empty();
            if(type == 2){
                generatePermissionTree(res.permissions);
            }else if(type == 1){
                generatePermissionUI(res.permissions);
            }
            // End Permission Matrix

            $('#selectRolePermission').css('display', 'block');
        });
    }

    // Permissions Tree
    function generatePermissionTree(permissions) {
        let tree = {};

        // BUILD TREE
        permissions.forEach(permission => {
            const parts = permission.name.split('.');

            let current = tree;

            parts.forEach((part, index) => {
                if (!current[part]) {
                    current[part] = {
                        __children: {},
                        __permission: null,
                        __full: parts.slice(0, index + 1).join('.')
                    };
                }

                if (index === parts.length - 1) {
                    current[part].__permission = permission;
                }

                current = current[part].__children;
            });
        });

        // RENDER TREE
        function renderNodes(nodes) {
            let html = '';

            Object.keys(nodes).sort((a, b) => a.localeCompare(b)).forEach(key => {
                const node = nodes[key];
                const hasChildren = Object.keys(node.__children).length > 0;
                const id = 'perm_' + node.__full.replaceAll('.', '_');

                html += `
                    <div class="permission-node">
                        <div class="d-flex align-items-center">
                            <span
                                class="permission-toggle ${!hasChildren ? 'empty' : ''}"
                                data-target="${id}_children"
                            >
                                ${hasChildren ? '▼' : '•'}
                            </span>

                            <div class="form-check mb-0">
                                <input
                                    type="checkbox"
                                    class="form-check-input permission-checkbox"
                                    id="${id}"
                                    value="${node.__permission ? node.__permission.id : ''}"
                                    data-full="${node.__full}"
                                    name="permissions[]"
                                >
                                <label
                                    class="form-check-label permission-label"
                                    for="${id}"
                                >
                                    ${key.replaceAll('_', ' ')}
                                </label>
                            </div>
                        </div>
                `;

                if (hasChildren) {
                    html += `
                        <div
                            class="permission-children"
                            id="${id}_children"
                        >
                            ${renderNodes(node.__children)}
                        </div>
                    `;
                }

                html += `
                    </div>
                `;
            });

            return html;
        }

        $('#permissionTree').html(renderNodes(tree));
    }

    // TOGGLE COLLAPSE
    $(document).on('click', '.permission-toggle', function () {
        const target = $(this).data('target');

        $('#' + target).toggle();

        $(this).text(
            $('#' + target).is(':visible')
                ? '▼'
                : '▶'
        );
    });

    // CHECK ALL CHILDREN
    $(document).on('change', '.permission-checkbox', function () {
        const checked = $(this).is(':checked');
        const wrapper = $(this).closest('.permission-node');

        wrapper.find('.permission-checkbox')
            .prop('checked', checked);
    });

    // AUTO CHECK PARENT
    $(document).on('change', '.permission-checkbox', function () {
        $('.permission-node').each(function () {
            const children = $(this).find('> .permission-children .permission-checkbox');

            if (children.length === 0) {
                return;
            }

            const checked = children.filter(':checked').length;

            $(this)
                .find('> div .permission-checkbox')
                .first()
                .prop('checked', checked > 0);
        });
    });
    // End Permissions Tree

    // Permissions UI
    let selectedPermissions = [];
    function generatePermissionUI(permissions) {
        // REBUILD BASE LAYOUT
        $('#permissionUI').html(`
            <div class="row">
                <!-- LEFT SIDE -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header fw-bold">Modules</div>
                        <div class="card-body">
                            <input
                                type="text"
                                class="form-control mb-3"
                                id="moduleSearch"
                                placeholder="Search module..."
                            >
                            <div id="moduleList" class="list-group"></div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT SIDE -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header fw-bold" id="permissionTitle" style="text-transform: capitalize">Permissions</div>
                        <div class="card-body">
                            <div id="permissionContent">
                                <div class="text-muted">Select module first</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `);

        let grouped = {};

        // GROUP PERMISSIONS
        $.each(permissions, function (_, permission) {
            let parts            = permission.name.split('.');
            let application      = parts[0];
            let module           = parts[1] || parts[0];
            let action           = parts[2] || 'access';
            let moduleKey        = application + '.' + module;
            let moduleLabel      = module.replaceAll('_', ' ');
            let applicationLabel = application.replaceAll('_', ' ');

            if (!grouped[moduleKey]) {
                grouped[moduleKey] = {
                    application: applicationLabel,
                    module: moduleLabel,
                    permissions: []
                };
            }

            grouped[moduleKey].permissions.push({
                id: permission.id,
                name: permission.name,
                action: action
            });
        });


        // LEFT SIDE MODULES
        let moduleHtml = '';
        Object.keys(grouped).sort((a, b) => {
            const moduleA = grouped[a].module.toLowerCase();
            const moduleB = grouped[b].module.toLowerCase();

            return moduleA.localeCompare(moduleB);
        }).forEach(moduleKey => {
            const item = grouped[moduleKey];

            moduleHtml += `
                <button
                    type="button"
                    class="list-group-item list-group-item-action module-item"
                    data-module="${moduleKey}"
                >
                    <div class="fw-bold text-capitalize">${item.module}</div>

                    <small class="text-muted text-uppercase">${item.application}</small>
                </button>
            `;
        });
        $('#moduleList').html(moduleHtml);

        // CLICK MODULE
        $(document).off('click', '.module-item').on('click', '.module-item', function () {
            $('.module-item').removeClass('active');
            $(this).addClass('active');

            let moduleKey = $(this).data('module');
            let item = grouped[moduleKey];

            $('#permissionTitle').html(`${item.module} Permissions`);

            const totalPermissions   = item.permissions.length;
            const checkedPermissions = item.permissions.filter(permission =>
                selectedPermissions.includes(permission.id.toString())
            ).length;

            const isAllChecked = totalPermissions === checkedPermissions;
            let permissionHtml = '';
            // SELECT ALL
            permissionHtml += `
                <div class="mb-3">
                    <div class="form-check">
                        <input
                            type="checkbox"
                            class="form-check-input select-all-permission"
                            id="select_all_${moduleKey}"
                            ${isAllChecked ? 'checked' : ''}
                        >
                        <label class="form-check-label fw-bold" for="select_all_${moduleKey}">
                            Select All
                        </label>
                    </div>
                </div>
            `;

            // PERMISSIONS
            $.each(item.permissions, function (_, permission) {
                permissionHtml += `
                    <div class="border rounded p-3 mb-2">
                        <div class="form-check">
                            <input
                                type="checkbox"
                                class="form-check-input permission-checkbox"
                                data-module="${moduleKey}"
                                id="perm_${permission.id}"
                                value="${permission.id}"

                                ${selectedPermissions.includes(permission.id.toString()) ? 'checked' : ''}
                            >

                            <label class="form-check-label" for="perm_${permission.id}">
                                <div class="fw-bold text-capitalize">
                                    ${permission.action.replaceAll('_', ' ')}
                                </div>
                                <small class="text-muted">${permission.name}</small>
                            </label>
                        </div>
                    </div>
                `;
            });

            $('#permissionContent').html(permissionHtml);
        });

        // SEARCH MODULE
        $('#moduleSearch').off('keyup').on('keyup', function () {
            let value = $(this).val().toLowerCase();

            $('.module-item').each(function () {
                $(this).toggle($(this).text().toLowerCase().includes(value));
            });
        });

        // SELECT ALL
        $(document).off('change', '.select-all-permission').on('change', '.select-all-permission', function () {
            let checked = $(this).is(':checked');
            $('.permission-checkbox').each(function () {
                const value = $(this).val();
                $(this).prop('checked', checked);
                if (checked) {
                    if (!selectedPermissions.includes(value)) {
                        selectedPermissions.push(value);
                    }
                } else {
                    selectedPermissions = selectedPermissions.filter(item => item != value);
                }
            });
        });

        $(document).off('change', '.permission-checkbox').on('change', '.permission-checkbox', function () {
            const value = $(this).val();
            const module = $(this).data('module');

            // STORE STATE
            if ($(this).is(':checked')) {
                if (!selectedPermissions.includes(value)) {
                    selectedPermissions.push(value);
                }
            } else {
                selectedPermissions = selectedPermissions.filter(item => item != value);
            }

            // UPDATE SELECT ALL
            const total     = $(`.permission-checkbox[data-module="${module}"]`).length;
            const checked   = $(`.permission-checkbox[data-module="${module}"]:checked`).length;

            $('.select-all-permission').prop('checked', total === checked);
        });
    }
    // End Permissions UI

    $('#crudForm').on('submit', function () {
        // remove old generated inputs
        $('.generated-permission').remove();

        // append all selected permissions
        selectedPermissions.forEach(permission => {
            $(this).append(`
                <input
                    type="hidden"
                    name="permissions[]"
                    value="${permission}"
                    class="generated-permission"
                >
            `);
        });
    });

</script>
