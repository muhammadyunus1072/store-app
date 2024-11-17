<div class='row'>
    @if (count($statusApprovals) > 0)
        {{-- NOTE --}}
        <div class="col-md-12 mb-3">
            <label>Catatan</label>
            <textarea class="form-control" cols="30" rows="4" wire:model="note"></textarea>
        </div>

        @foreach ($statusApprovals as $item)
            <button class='btn btn-primary mb-3' wire:click="requestSubmit('{{ $item['id'] }}')">
                {{ $item['text'] }}
            </button>
        @endforeach
    @endif
</div>
