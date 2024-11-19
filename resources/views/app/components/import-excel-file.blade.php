<div class="row">
    @foreach ($import_excel as $index => $excel)
        <div class="mb-4 {{ $excel['class'] }}" wire:key="import_excel_{{ $index }}">
            <label for="import_excel_{{ $index }}" class="form-label">{{ $excel['name'] }}</label>
            <input type="file" wire:model="import_excel.{{ $index }}.data" id="import_excel_{{ $index }}" class="form-control">
            
            @error('import_excel.' . $index . '.data')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    @endforeach
</div>
