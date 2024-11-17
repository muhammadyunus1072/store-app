<?php

namespace App\Livewire\Document\Transaction\Approval;

use App\Helpers\General\Alert;
use App\Permissions\AccessDocument;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Traits\Livewire\WithDatatable;
use App\Permissions\PermissionHelper;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Core\User\UserRepository;
use App\Repositories\Document\Transaction\ApprovalRepository;

class Datatable extends Component
{
    use WithDatatable;

    public $isCanUpdate;
    public $isCanDelete;

    // Delete Dialog
    public $targetDeleteId;

    public function onMount()
    {
        $this->sortDirection = 'desc';
        $authUser = UserRepository::authenticatedUser();
        $this->isCanUpdate = $authUser->hasPermissionTo(PermissionHelper::transform(AccessDocument::APPROVAL, PermissionHelper::TYPE_UPDATE));
        $this->isCanDelete = $authUser->hasPermissionTo(PermissionHelper::transform(AccessDocument::APPROVAL, PermissionHelper::TYPE_DELETE));
    }

    #[On('on-delete-dialog-confirm')]
    public function onDialogDeleteConfirm()
    {
        if (!$this->isCanDelete || $this->targetDeleteId == null) {
            return;
        }

        ApprovalRepository::delete(Crypt::decrypt($this->targetDeleteId));
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
                    $id = Crypt::encrypt($item->id);

                    $showUrl = route('approval.show', $id);
                    $showHtml = "<div class='col-auto mb-2'>
                        <a class='btn btn-info btn-sm' href='$showUrl'>
                            <i class='ki-solid ki-eye fs-1'></i>
                            Lihat
                        </a>
                    </div>";

                    return "<div class='row'>
                        $showHtml 
                    </div>";
                },
            ],
            [
                'searchable' => false,
                'key' => 'created_at',
                'name' => 'Tanggal',
                'render' => function ($item) {
                    return $item->created_at->translatedFormat('d F Y, H:i');
                }
            ],
            [
                'key' => 'number',
                'name' => 'Nomor',
                'render' => function ($item) {
                    return $item->number;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Status',
                'render' => function ($item) {
                    return $item->beautifyStatus();
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Sumber',
                'render' => function ($item) {
                    return $item->remarksUrlButton();
                }
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return ApprovalRepository::datatable();
    }

    public function getView(): string
    {
        return 'livewire.document.transaction.approval.datatable';
    }
}
