<?php

namespace App\Livewire\Document\Transaction\ApprovalStatus;

use App\Helpers\General\Alert;
use App\Permissions\AccessDocument;
use App\Permissions\PermissionHelper;
use App\Repositories\Core\User\UserRepository;
use Livewire\Component;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Document\Transaction\ApprovalRepository;
use App\Repositories\Document\Transaction\ApprovalStatusRepository;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class Create extends Component
{
    public $approvalId;

    public $approvalUserId;
    public $statusApprovalId;
    public $note;

    // Helpers
    public $isCanCreate = false;
    public $statusApprovals = [];

    public function render()
    {
        return view('livewire.document.transaction.approval-status.create');
    }

    public function mount()
    {
        $authUser = UserRepository::authenticatedUser();

        $this->isCanCreate = $authUser->hasPermissionTo(PermissionHelper::transform(AccessDocument::APPROVAL_STATUS, PermissionHelper::TYPE_CREATE));
        if (!$this->isCanCreate) {
            return;
        }

        $this->getCurrentApproval();
    }

    #[On('on-delete-status-approval')]
    public function getCurrentApproval()
    {
        $this->statusApprovals = [];
        $authUser = UserRepository::authenticatedUser();

        $approval = ApprovalRepository::find(Crypt::decrypt($this->approvalId));
        $approvalUser = $approval->findCurrentApprovalUser($authUser->id);

        if (!empty($approvalUser)) {
            $this->approvalUserId = Crypt::encrypt($approvalUser->id);

            foreach ($approvalUser->statusApprovals as $item) {
                $this->statusApprovals[] = [
                    'id' => Crypt::encrypt($item->id),
                    'text' => $item->name,
                    'color' => $item->color,
                    'text_color' => $item->text_color,
                ];
            }
        }
    }

    #[On('on-dialog-request-submit-confirm')]
    public function onDialogDeleteConfirm()
    {
        if (!$this->isCanCreate) {
            return;
        }

        ApprovalStatusRepository::create([
            'user_id' => Auth::id(),
            'approval_id' => Crypt::decrypt($this->approvalId),
            'status_approval_id' => Crypt::decrypt($this->statusApprovalId),
            'approval_user_id' => Crypt::decrypt($this->approvalUserId),
            'note' => $this->note,
        ]);

        $this->getCurrentApproval();

        Alert::success($this, 'Berhasil', 'Data berhasil dihapus');
        $this->dispatch('on-submit-status-approval');
    }

    #[On('on-dialog-request-submit-cancel')]
    public function onDialogDeleteCancel()
    {
        $this->statusApprovalId = null;
    }

    public function requestSubmit($statusApprovalId)
    {
        $this->statusApprovalId = $statusApprovalId;

        Alert::confirmation(
            $this,
            Alert::ICON_QUESTION,
            "Konfirmasi",
            "Apakah Anda Yakin?",
            "on-dialog-request-submit-confirm",
            "on-dialog-request-submit-cancel",
            "Iya",
            "Tidak",
        );
    }
}
