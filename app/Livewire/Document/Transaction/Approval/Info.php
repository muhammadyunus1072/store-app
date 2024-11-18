<?php

namespace App\Livewire\Document\Transaction\Approval;

use Livewire\Component;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Document\Transaction\ApprovalRepository;
use Livewire\Attributes\On;

class Info extends Component
{
    public $approvalId;

    public $isSequentially = false;
    public $isDoneWhenAllSubmitted = false;
    public $approvalUsers = [];
    public $remarksUrlButton = "";
    public $status = "";

    public function render()
    {
        return view('livewire.document.transaction.approval.info');
    }

    public function mount()
    {
        $approval = ApprovalRepository::find(Crypt::decrypt($this->approvalId));

        $this->isSequentially = $approval->is_sequentially;
        $this->isDoneWhenAllSubmitted = $approval->is_done_when_all_submitted;
        $this->remarksUrlButton = $approval->remarksUrlButton();
        $this->status = $approval->beautifyStatus("fs-5");

        $this->getApprovalUsers();
    }

    #[On('on-submit-status-approval')]
    #[On('on-delete-status-approval')]
    public function getApprovalUsers()
    {
        $approval = ApprovalRepository::find(Crypt::decrypt($this->approvalId));
        foreach ($approval->approvalUsers as $index => $approvalUser) {
            $this->approvalUsers[$index] = [
                'name' => $approvalUser->user->name,
                "position" => $approvalUser->position,
                'status_submitted' => $approvalUser->beautifyStatusSubmitted(),
            ];
        }
    }
}
