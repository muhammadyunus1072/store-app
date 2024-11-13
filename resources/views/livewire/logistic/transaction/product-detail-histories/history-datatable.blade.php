<div class="accordion mt-4" id="accordionExample" wire:ignore.self>
    <div class="accordion-item" wire:ignore.self>
        <h2 class="accordion-header" id="headingOne" wire:ignore>
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne"
                aria-expanded="true" aria-controls="collapseOne">
                <label class='fw-bold'>Kartu Stok</label>
            </button>
        </h2>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
            data-bs-parent="#accordionExample" wire:ignore.self>
            <div class="accordion-body" wire:ignore.self>
                <div class='row'>
                    <div class='col-md-auto mb-4'>
                        <button class="btn btn-info" wire:click="refreshRemarks">
                            <i class='ki-solid ki-arrows-circle'></i>
                            Refresh
                        </button>
                    </div>
                    @if ($statusMessage)
                        <div class='col-md mb-4'>
                            <div class='alert alert-danger'>
                                <div class='fw-bold'>TRANSAKSI TIDAK BERHASIL</div>
                                {{ $statusMessage }}
                            </div>
                        </div>
                    @endif
                </div>

                @include('livewire.livewire-datatable')
            </div>
        </div>
    </div>
</div>
