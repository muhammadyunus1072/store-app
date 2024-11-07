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
                <button class="btn btn-info mb-4" wire:click="$refresh">
                    <i class='ki-solid ki-arrows-circle'></i>
                    Refresh
                </button>

                @include('livewire.livewire-datatable')
            </div>
        </div>
    </div>
</div>
