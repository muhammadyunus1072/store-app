<?php

namespace App\Livewire\Document\Transaction\Approval;

use Livewire\Component;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Document\Transaction\ApprovalRepository;


class RemarksDocument extends Component
{
    public $approvalId;
    public $approvalView;

    public function render()
    {
        return view('livewire.document.transaction.approval.remarks-document');
    }

    public function mount()
    {
        $approval = ApprovalRepository::find(Crypt::decrypt($this->approvalId));
        $this->approvalView = $approval->remarks->approvalView();
    }
}
