{{-- Export Data --}}
<div class="row col-12 py-3">
    <div class="col-auto">
        <label>Export Data:</label>
    </div>
    <div class="col-auto">
        <button class="btn btn-light-success btn-sm"
            wire:click="$dispatch('export', { type: '{{ App\Helpers\General\ExportHelper::TYPE_EXCEL }}' })">
            <i class="fa fa-file-excel"></i>
            Export Excel
        </button>
    </div>
    <div class="col-auto">
        <button class="btn btn-light-danger btn-sm"
            wire:click="$dispatch('export', { type: '{{ App\Helpers\General\ExportHelper::TYPE_PDF }}' })">
            <i class="fa fa-file-pdf"></i>
            Export PDF
        </button>
    </div>
</div>