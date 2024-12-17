<?php

namespace App\Livewire\Rsmh\Sakti\InterkoneksiSaktiCoa;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Helpers\General\Alert;
use App\Permissions\AccessInterkoneksiSakti;
use App\Permissions\AccessPurchasing;
use App\Permissions\PermissionHelper;
use Illuminate\Support\Facades\Crypt;
use App\Traits\Livewire\WithDatatable;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Core\User\UserRepository;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiCoa\InterkoneksiSaktiCoaRepository;

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
        $this->isCanUpdate = $authUser->hasPermissionTo(PermissionHelper::transform(AccessInterkoneksiSakti::INTERKONEKSI_SAKTI_COA, PermissionHelper::TYPE_UPDATE));
        $this->isCanDelete = $authUser->hasPermissionTo(PermissionHelper::transform(AccessInterkoneksiSakti::INTERKONEKSI_SAKTI_COA, PermissionHelper::TYPE_DELETE));
    }

    #[On('on-delete-dialog-confirm')]
    public function onDialogDeleteConfirm()
    {
        if (!$this->isCanDelete || $this->targetDeleteId == null) {
            return;
        }
        
        InterkoneksiSaktiCoaRepository::delete(Crypt::decrypt($this->targetDeleteId));
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
                    if ($this->isCanUpdate) {
                        $editUrl = route('interkoneksi_sakti_coa.edit', $id);
                        $editHtml = "<div class='col-auto mb-2'>
                            <a class='btn btn-primary btn-sm' href='$editUrl'>
                                <i class='ki-duotone ki-notepad-edit fs-1'>
                                    <span class='path1'></span>
                                    <span class='path2'></span>
                                </i>
                                Ubah
                            </a>
                        </div>";
                    }

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
                'key' => 'kode',
                'name' => 'Kode',
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return InterkoneksiSaktiCoaRepository::datatable();
    }

    public function getView(): string
    {
        return 'livewire.rsmh.sakti.interkoneksi-sakti-coa.datatable';
    }
}
