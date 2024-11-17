<?php

namespace App\Livewire\Document\Transaction\ApprovalStatus;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Helpers\General\Alert;
use App\Permissions\AccessDocument;
use Illuminate\Support\Facades\Auth;
use App\Permissions\PermissionHelper;
use Illuminate\Support\Facades\Crypt;
use App\Traits\Livewire\WithDatatable;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Core\User\UserRepository;
use App\Repositories\Document\Transaction\ApprovalStatusRepository;

class Datatable extends Component
{
    use WithDatatable;

    protected $listeners = ['on-submit-status-approval' => '$refresh'];

    public $approvalId;

    public $isCanDelete;
    public $showSelectPageLength = false;
    public $showKeywordFilter = false;


    // Delete Dialog
    public $targetDeleteId;

    public function onMount()
    {
        $authUser = UserRepository::authenticatedUser();
        $this->isCanDelete = $authUser->hasPermissionTo(PermissionHelper::transform(AccessDocument::APPROVAL_STATUS, PermissionHelper::TYPE_DELETE));
    }

    #[On('on-submit-status-approval')]
    public function onSubmitStatusApproval()
    {
        $this->refresh();
    }

    #[On('on-delete-dialog-confirm')]
    public function onDialogDeleteConfirm()
    {
        if (!$this->isCanDelete || $this->targetDeleteId == null) {
            return;
        }

        ApprovalStatusRepository::delete(Crypt::decrypt($this->targetDeleteId));
        Alert::success($this, 'Berhasil', 'Data berhasil dihapus');

        $this->dispatch('on-delete-status-approval');
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
                'sortable' => false,
                'key' => 'created_at',
                'name' => 'Tanggal',
                'render' => function ($item) {
                    return Carbon::parse($item->created_at)->translatedFormat('d F Y, H:i');
                }
            ],
            [
                'sortable' => false,
                'name' => 'Status',
                'render' => function ($item) {
                    return $item->status_approval_name;
                }
            ],
            [
                'sortable' => false,
                'key' => 'note',
                'name' => 'Catatan',
                'render' => function ($item) {
                    return $item->note;
                }
            ],
            [
                'sortable' => false,
                'name' => 'Oleh',
                'render' => function ($item) {
                    return $item->user->name;
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Aksi',
                'render' => function ($item) {
                    $html = "";
                    $id = Crypt::encrypt($item->id);

                    if ($this->isCanDelete && Auth::id() == $item->user_id) {
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
        return ApprovalStatusRepository::datatable(Crypt::decrypt($this->approvalId));
    }

    public function getView(): string
    {
        return 'livewire.document.transaction.approval-status.datatable';
    }
}
