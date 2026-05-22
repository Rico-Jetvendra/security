<div class="modal fade" id="crudModal" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST" id="crudForm">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <span id="modalTitle">Tambah</span> {{ $title }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    @foreach ($fields as $field)
                        <div class="mb-3">
                            <label class="form-label" for="{{ $field['id'] }}">{{ $field['label'] }}</label>

                            <x-form.dynamic
                                :field="$field"
                                :selects="$selects ?? []"
                            />
                        </div>
                    @endforeach
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>

            </form>

        </div>
    </div>
</div>
