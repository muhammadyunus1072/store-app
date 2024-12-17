
<form wire:submit="store">
    <div class='row border rounded p-4 mb-4'>
        <div class="col-md-4 mb-4">
            <label class='fw-bold'>Default KBKI yang terpilih</label>
            <select class="form-select" wire:model="barang_interkoneksi_sakti_kbki_id">
                @foreach ($barang_interkoneksi_sakti_kbki_id_choice as $value => $text)
                    <option value="{{ $value }}">{{ $text }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 mb-4">
            <label>Default isi persentase tkdn</label>
            <input type="text" class="form-control" wire:model="barang_persentase_tkdn" />
        </div>
        <div class="col-md-4 mb-4">
            <label>Default isi kategori pdn</label>
            <input type="text" class="form-control" wire:model="barang_kategori_pdn" />
        </div>
        <div class="col-md-4 mb-4">
            <label>Default isi kode uakpb</label>
            <input type="text" class="form-control" wire:model="barang_kode_uakpb" />
        </div>
        <div class="col-md-4 mb-4">
            <label>Default isi volume sub output</label>
            <input type="text" class="form-control" wire:model="coa_vol_sub_output" />
        </div>
        <div class="col-md-4 mb-4">
            <label>Default Coa 12 yang terpilih</label>
            <select class="form-select" wire:model="header_interkoneksi_sakti_coa_12_id">
                @foreach ($header_interkoneksi_sakti_coa_12_id_choice as $value => $text)
                    <option value="{{ $value }}">{{ $text }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-4">
            <label>Default isi kode satker</label>
            <input type="text" class="form-control" wire:model="header_kode_satker" />
        </div>
        <div class="col-md-4 mb-4">
            <label>Default isi kategori</label>
            <input type="text" class="form-control" wire:model="header_kategori" />
        </div>
        <div class="col-md-4 mb-4">
            <label>Default isi nama penerima</label>
            <input type="text" class="form-control" wire:model="header_nama_penerima" />
        </div>
        <div class="col-md-4 mb-4">
            <label>Default isi no rekening</label>
            <input type="text" class="form-control" wire:model="header_no_rekening" />
        </div>
        <div class="col-md-4 mb-4">
            <label>Default isi kode mata uang</label>
            <input type="text" class="form-control" wire:model="header_kode_mata_uang" />
        </div>
        <div class="col-md-4 mb-4">
            <label>Default isi nilai kurs</label>
            <input type="text" class="form-control" wire:model="header_nilai_kurs" />
        </div>
        <div class="col-md-4 mb-4">
            <label>Default isi npwp</label>
            <input type="text" class="form-control" wire:model="header_npwp" />
        </div>
        <div class="col-md-4 mb-4">
            <label>Default isi uraian dokumen</label>
            <input type="text" class="form-control" wire:model="header_uraian_dokumen" />
        </div>
    </div>

    <button type="submit" class="btn btn-success mt-3">
        <i class='ki-duotone ki-check fs-1'></i>
        Simpan
    </button>
</form>
