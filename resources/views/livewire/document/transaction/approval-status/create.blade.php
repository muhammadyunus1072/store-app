@if (count($statusApprovals) > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h4 class='card-title'>Tindak Lanjut Persetujuan</h4>
        </div>
        <div class="card-body">
            <div class='row'>
                {{-- NOTE --}}
                <div class="col-md-12 mb-3">
                    <label>Catatan</label>
                    <textarea class="form-control" cols="30" rows="4" wire:model="note"></textarea>
                </div>

                @foreach ($statusApprovals as $item)
                    <button class='btn mb-3'
                        style="background-color: {{ $item['color'] }}; color: {{ $item['text_color'] }}"
                        wire:click="requestSubmit('{{ $item['id'] }}')">
                        {{ $item['text'] }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
@else
    <div></div>
@endif
