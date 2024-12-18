<?php

namespace App\Livewire\Rsmh\Sakti\InterkoneksiSaktiDetailBarang;

use Exception;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Helpers\General\Alert;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\Core\UserStateHandler;
use App\Repositories\Rsmh\Sakti\GenerateInterkoneksiSaktiDetailBarang\GenerateInterkoneksiSaktiDetailBarangRepository;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiKbki\InterkoneksiSaktiKbkiRepository;

class Detail extends Component
{
    public $isGenerateProcess;

    public $warehouseId;
    #[Validate('required', message: 'Tanggal Mulai Harus Diisi', onUpdate: false)]
    public $dateStart;
    #[Validate('required', message: 'Tanggal Akhir Harus Diisi', onUpdate: false)]
    public $dateEnd;

    
    public function mount()
    {
        $this->refreshProgress();
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        $this->redirectRoute('interkoneksi_sakti_detail_barang.index');
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('interkoneksi_sakti_detail_barang.index');
    }

    public function refreshProgress()
    {
        $this->isGenerateProcess = GenerateInterkoneksiSaktiDetailBarangRepository::findBy(whereClause:[
            ['is_done', false]
        ]);
    }

    public function store()
    {
        if(!$this->warehouseId)
        {
            Alert::fail($this, "Gagal", "Gudang Belum Diinput");
            return;
        }
        if($this->isGenerateProcess)
        {
            Alert::fail($this, "Gagal", "Sudah Terdapat Proses");
            return;
        }
        $this->validate();

        try {
            DB::beginTransaction();
            $data = GenerateInterkoneksiSaktiDetailBarangRepository::getData(Crypt::decrypt($this->warehouseId), $this->dateStart, $this->dateEnd);
            $validatedData = [
                'warehouse_id' => Crypt::decrypt($this->warehouseId),
                'date_start' => $this->dateStart,
                'date_end' => $this->dateEnd,
            ];
            $obj = GenerateInterkoneksiSaktiDetailBarangRepository::create($validatedData);
            DB::commit();

            Alert::confirmation(
                $this,
                Alert::ICON_SUCCESS,
                "Berhasil",
                "Data Berhasil Diperbarui",
                "on-dialog-confirm",
                "on-dialog-cancel",
                "Oke",
                "Tutup",
            );
        } catch (Exception $e) {
            DB::rollBack();
            Alert::fail($this, "Gagal", $e->getMessage());
        }
    }

    public function showGenerateData()
    {
        $this->dateStart = null;
        $this->dateEnd = null;
    }

    public function render()
    {
        return view('livewire.rsmh.sakti.interkoneksi-sakti-detail-barang.detail');
    }
}
