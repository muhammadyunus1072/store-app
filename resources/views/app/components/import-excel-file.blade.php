<div class="row">
    @foreach ($import_excel as $index => $excel)
        <div class="mb-4 border rounded p-4 {{ $excel['class'] }}" wire:key="import_excel_{{ $index }}">
            <label for="import_excel_{{ $index }}" class="form-label">{{ $excel['name'] }}</label>
            <hr>
            <input type="file" wire:model="import_excel.{{ $index }}.data" id="import_excel_{{ $index }}"
                class="form-control">

            @error('import_excel.' . $index . '.data')
                <div class="text-danger">{{ $message }}</div>
            @enderror

            <button type="button"
                wire:click="{{ isset($excel['storeHandler']) && $excel['storeHandler'] ? $excel['storeHandler'] : 'storeImport' }}('{{ $index }}')"
                class="btn btn-success mt-3" wire:loading.attr="disabled">
                <div wire:loading>
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Loading...
                </div>

                <div class='d-flex align-items-center' wire:loading.class="d-none">
                    <i class='ki-duotone ki-check fs-1'></i>
                    Simpan
                </div>
            </button>
        </div>
    @endforeach
</div>
