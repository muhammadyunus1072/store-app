<?php

namespace App\Livewire\Document\Transaction\ApprovalHistory;

use Carbon\Carbon;
use App\Helpers\Alert;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Traits\Livewire\WithDatatable;
use App\Permissions\PermissionHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Core\User\UserRepository;
use App\Repositories\Document\Transaction\ApprovalHistoryRepository;

class Datatable extends Component
{
    use WithDatatable;
    public $approvalId;
    
    public $isCanUpdate;
    public $isCanDelete;
    public $show_filter = false;
    public $keyword_filter = false;

    // Delete Dialog
    public $targetDeleteId;

    public function onMount()
    {
        $this->length = 5;
        $authUser = UserRepository::authenticatedUser();
        $this->isCanUpdate = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_APPROVAL, PermissionHelper::TYPE_UPDATE));
        $this->isCanDelete = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_APPROVAL, PermissionHelper::TYPE_DELETE));
    }

    #[On('on-delete-dialog-confirm')]
    public function onDialogDeleteConfirm()
    {
        if (!$this->isCanDelete || $this->targetDeleteId == null) {
            return;
        }
        
        ApprovalHistoryRepository::delete(Crypt::decrypt($this->targetDeleteId));
        Alert::success($this, 'Berhasil', 'Data berhasil dihapus');
        $this->dispatch('refreshApproval');
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
                'key' => 'created_at',
                'searchable' => false,
                'name' => 'Tanggal',
                'render' => function($item)
                {
                    return Carbon::parse($item->created_at)->translatedFormat('d F Y, H:i');
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Oleh',
                'render' => function($item)
                {
                    return $item->user->name;
                }
            ],
            [
                'key' => 'note',
                'searchable' => false,
                'name' => 'Keterangan',
                'render' => function($item)
                {
                    return $item->note;
                }
            ],
            [
                'key' => 'note',
                'searchable' => false,
                'name' => 'Catatan Status',
                'render' => function($item)
                {
                    return $item->statusApproval->name;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Aksi',
                'render' => function($item)
                {
                    $html = "";
                    $id = Crypt::encrypt($item->id);
                    if(Auth::id() == $item->user_id){
                        $html = "<div class='btn-group'>
                        <button class='btn p-0 m-0' wire:click=\"showDeleteDialog('$id')\">
                            <i class='ki-duotone ki-trash fs-1 text-danger'>
                                <span class='path1'></span>
                                <span class='path2'></span>
                                <span class='path3'></span>
                                <span class='path4'></span>
                                <span class='path5'></span>
                            </i>
                        </button>
                    </div>";
                    };
                    return $html;
                }
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return ApprovalHistoryRepository::datatable(Crypt::decrypt($this->approvalId));
    }

    public function getView(): string
    {
        return 'livewire.document.transaction.approval-history.datatable';
    }
}
