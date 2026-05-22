function initCrud({ routes, fields, columns, permissions, basePermission }) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const modal         = $('#crudModal');
    const form          = $('#crudForm');
    const defaultSearch = $('#defaultSearch').val();
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
    });

    // OPEN EDIT
    $(document).on('click', '.btn-edit', function () {
        const id = $(this).data('id');

        form.attr('action', routes.update(id));
        $('#formMethod').val('PUT');
        $('#modalTitle').text('Edit');

        $.get(routes.edit(id), res => {
            for(let key in fields){
                if(key !== fields[key]){
                    var el = $('#' + key);

                    if(fields[key] == 'checkbox'){
                        el.prop('checked', true);
                    }

                    if (el[0].tomselect) {
                        el[0].tomselect.setValue(res[key]);
                    } else {
                        el.val(res[key]).trigger('change');
                    }

                    el.val(res[key]).trigger('change');
                }
                $('#' + fields[key]).val(res[fields[key]]);
            }
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
}
