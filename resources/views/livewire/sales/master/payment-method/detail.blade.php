<div class="row">
    <form wire:submit="store">
        <div class='row'>
            <div class="col-md-10 mb-4">
                <label>Nama Metode Pembayaran</label>
                <input placeholder="Nama Metode Pembayaran" type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" />
    
                @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="col-md-10 mb-4">
                <label>Kode</label>
                <input placeholder="Kode" type="text" class="form-control @error('code') is-invalid @enderror" wire:model="code" />
    
                @error('code')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="col-md-10 mb-4">
                <label>Jenis Biaya Admin</label>
                <select class="form-select w-100" wire:model='type'>
                    @php $isFound = false; @endphp

                    @foreach ($type_choices as $type_value => $type_name)
                        @php $isFound = $isFound || $type_value == $type; @endphp
                        <option value="{{ $type_value }}" {{$isFound ? 'selected' : ''}}>{{ $type_name }}</option>
                    @endforeach
                </select>
    
                @error('type')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="col-md-10 mb-4">
                <label>Nilai Biaya Admin</label>
                <input placeholder="Nilai Biaya Admin" type="text" class="form-control currency @error('amount') is-invalid @enderror" wire:model="amount" />
    
                @error('amount')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <!-- Active Option -->
            
            <div class="col-md-4 mb-4 row align-items-end">
                <div class="form-check m-2">
                    <input class="form-check-input" type="checkbox" wire:model="is_active">
                    <label class="form-label ms-2 mb-2">
                        Penanda Aktif
                    </label>
                </div>
            </div>
            <div class="col-md-10 mb-4">
                <button type="submit" class="btn btn-success mt-3 w-100">
                    Simpan
                </button>
            </div>

    
    </form>
</div>

@include('js.imask')

