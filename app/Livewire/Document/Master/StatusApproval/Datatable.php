<?php

namespace App\Livewire\Document\Master\StatusApproval;

use App\Helpers\General\Alert;
use App\Permissions\AccessDocument;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Traits\Livewire\WithDatatable;
use App\Permissions\PermissionHelper;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Core\User\UserRepository;
use App\Repositories\Document\Master\StatusApproval\StatusApprovalRepository;

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
        $this->isCanUpdate = $authUser->hasPermissionTo(PermissionHelper::transform(AccessDocument::STATUS_APPROVAL, PermissionHelper::TYPE_UPDATE));
        $this->isCanDelete = $authUser->hasPermissionTo(PermissionHelper::transform(AccessDocument::STATUS_APPROVAL, PermissionHelper::TYPE_DELETE));
    }

    #[On('on-delete-dialog-confirm')]
    public function onDialogDeleteConfirm()
    {
        if (!$this->isCanDelete || $this->targetDeleteId == null) {
            return;
        }

        StatusApprovalRepository::delete(Crypt::decrypt($this->targetDeleteId));
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
                        $editUrl = route('status_approval.edit', $id);
                        $editHtml = "<div class='col-auto mb-2'>
                            <a class='btn btn-primary btn-sm' href='$editUrl'>
                                <i class='ki-duotone ki-notepad-edit fs-1'>
                                    <span class='path1'></span>
                                    <span class='path2'></span>
                                </i>
                                Edit
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
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nama',
                'render' => function ($item) {
                    return $item->name;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Warna',
                'render' => function ($item) {
                    $html = "Warna Background: <input type='color' value='$item->color' disabled>";
                    $html .= "<br>Warna Tulisan: <input type='color' value='$item->text_color' disabled>";
                    return $html;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Penanda Selesai',
                'render' => function ($item) {
                    return $item->is_trigger_done ? "<div class='badge badge-success'>Iya</div>" : "<div class='badge badge-secondary'>Tidak</div>";
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Penanda Batal',
                'render' => function ($item) {
                    return $item->is_trigger_cancel ? "<div class='badge badge-success'>Iya</div>" : "<div class='badge badge-secondary'>Tidak</div>";
                }
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return StatusApprovalRepository::datatable();
    }

    public function getView(): string
    {
        return 'livewire.document.master.status-approval.datatable';
    }
}
