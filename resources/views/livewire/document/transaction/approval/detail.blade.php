
<div class="col-md-12">
    <input type="hidden" id="id" name="id" value="">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Informasi Detail Persetujuan</h4>
        </div>
        <div class="card-body">
            <div class="row">
                @livewire($object['component'], $object['data'])

                <div class="col-md-5 border p-3 d-flex flex-column">
                    <div class="w-100">
                        <h3>Riwayat Status</h3>
                        <livewire:document.transaction.approval-history.datatable :approvalId="$objId">
                    </div>

                    <div class="align-self-center mb-0 border w-100">
                        
                        @if ($is_enabled)
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea cols="30" rows="5" class="form-control" wire:model="history_note"></textarea>
                            </div>
                        @endif
                        @if ($is_enabled)
                            @foreach ($status_approval_choice as $status_approval)
                                
                            <button type="button" wire:click="submit('{{$status_approval['id']}}')" class="d-block w-100 mt-2 btn btn-primary">
                                {{$status_approval['name']}}
                            </button>  
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>