<?php

namespace App\Livewire\Rsmh\Sakti\InterkoneksiSaktiDetailBarang;

use App\Helpers\General\Alert;
use App\Models\Rsmh\Sakti\InterkoneksiSaktiKbki;
use App\Permissions\AccessInterkoneksiSakti;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Traits\Livewire\WithDatatable;
use App\Permissions\PermissionHelper;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Core\User\UserRepository;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiDetailBarang\InterkoneksiSaktiDetailBarangRepository;

class Datatable extends Component
{
    use WithDatatable;

    public $isCanUpdate;
    public $isCanDelete;

    // Delete Dialog
    public $targetDeleteId;

    public function onMount()
    {
        $authUser = UserRepository::authenticatedUser();
        $this->isCanUpdate = $authUser->hasPermissionTo(PermissionHelper::transform(AccessInterkoneksiSakti::INTERKONEKSI_SAKTI_DETAIL_BARANG, PermissionHelper::TYPE_UPDATE));
        $this->isCanDelete = $authUser->hasPermissionTo(PermissionHelper::transform(AccessInterkoneksiSakti::INTERKONEKSI_SAKTI_DETAIL_BARANG, PermissionHelper::TYPE_DELETE));
    }

    #[On('on-delete-dialog-confirm')]
    public function onDialogDeleteConfirm()
    {
        if (!$this->isCanDelete || $this->targetDeleteId == null) {
            return;
        }
        
        InterkoneksiSaktiDetailBarangRepository::delete(Crypt::decrypt($this->targetDeleteId));
        Alert::success($this, 'Berhasil', 'Data berhasil dihapus');
    }

    #[On('on-delete-dialog-cancel')]
    public function onDialogDeleteCancel()
    {
        $this->targetDeleteId = null;
    }

    public function showDeleteDialog($id)
    {
        $this->targetDeleteId = $id;

        Alert::confirmation(
            $this,
            Alert::ICON_QUESTION,
            "Hapus Data",
            "Apakah Anda Yakin Ingin Menghapus Data Ini ?",
            "on-delete-dialog-confirm",
            "on-delete-dialog-cancel",
            "Hapus",
            "Batal",
        );
    }

    public function getColumns(): array
    {
        return [
            [
                'name' => 'Aksi',
                'sortable' => false,
                'searchable' => false,
                'render' => function ($item) {

                    $editHtml = "";
                    $id = Crypt::encrypt($item->id);

                    $destroyHtml = "";
                    if ($this->isCanDelete) {
                        $destroyHtml = "<div class='col-auto mb-2'>
                            <button class='btn btn-danger btn-sm m-0' 
                                wire:click=\"showDeleteDialog('$id')\">
                                <i class='ki-duotone ki-trash fs-1'>
                                    <span class='path1'></span>
                                    <span class='path2'></span>
                                    <span class='path3'></span>
                                    <span class='path4'></span>
                                    <span class='path5'></span>
                                </i>
                                Hapus
                            </button>
                        </div>";
                    }

                    $html = "<div class='row'>
                        $editHtml $destroyHtml 
                    </div>";

                    return $html;
                },
            ],
            [
                'key' => 'no_dokumen',
                'name' => 'No Dokumen',
            ],
            [
                'key' => 'kode_barang',
                'name' => 'Kode Barang',
            ],
            [
                'key' => 'jumlah_barang',
                'name' => 'Jumlah Barang',
            ],
            [
                'key' => 'harga_satuan',
                'name' => 'Harga Satuan',
            ],
            [
                'key' => 'kode_uakpb',
                'name' => 'Kode UAKPB',
            ],
            [
                'key' => 'kode_kbki',
                'name' => 'kode KBKI',
            ],
            [
                'key' => 'kode_coa',
                'name' => 'Kode COA',
            ],
            [
                'key' => 'persentase_tkdn',
                'name' => 'Persentase TKDN',
            ],
            [
                'key' => 'kategori_pdn',
                'name' => 'Kategori PDN',
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return InterkoneksiSaktiDetailBarangRepository::datatable();
    }

    public function getView(): string
    {
        return 'livewire.rsmh.sakti.interkoneksi-sakti-detail-barang.datatable';
    }
}
