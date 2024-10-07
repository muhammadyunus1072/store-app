<?php

namespace App\Livewire\Document\Transaction\Approval;

use Exception;
use App\Helpers\Alert;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Helpers\NumberFormatter;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Document\Transaction\ApprovalRepository;
use App\Repositories\Document\Transaction\ApprovalUserRepository;
use App\Repositories\Document\Transaction\ApprovalHistoryRepository;
use App\Repositories\Document\Master\StatusApproval\StatusApprovalRepository;


class Detail extends Component
{
    public $objId;
    public $object;

    public $is_enabled;
    public $created_at;
    public $number;
    public $creator_name;
    public $note;

    public $status_approval_choice = [];

    public $history_note;

    public function mount()
    {
        $this->status_approval_choice = StatusApprovalRepository::getAll();
        if ($this->objId) {
            $this->getApproval();
        }
    }

    #[On('refreshApproval')]
    public function getApproval()
    {
        $id = Crypt::decrypt($this->objId);
        $approval = ApprovalRepository::findWithDetails($id);
        
        $this->created_at = $approval->created_at;
        $this->number = $approval->remarks_table->number;
        $this->creator_name = $approval->creator->name;
        $this->note = $approval->note;

        $this->is_enabled = $approval->is_enabled();
        //  ? true : false;

        $this->object = ApprovalRepository::viewShow($id);
    }
    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('approval.edit', $this->objId);
        } else {
            $this->redirectRoute('approval.create');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('approval.index');
    }

    public function submit($status_id)
    {
        $this->dispatch('consoleLog', $status_id);
        $this->dispatch('consoleLog', $this->history_note);

        try {
            DB::beginTransaction();
            
            $validatedData = [
                'approval_id' => Crypt::decrypt($this->objId),
                'user_id' => Auth::id(),
                'status_id' => Crypt::decrypt($status_id),
                'note' => $this->history_note
            ];

            ApprovalHistoryRepository::create($validatedData);

            DB::commit();

            Alert::confirmation(
                $this,
                Alert::ICON_SUCCESS,
                "Berhasil",
                "Akses Berhasil Diperbarui",
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

    public function render()
    {
        return view('livewire.document.transaction.approval.detail');
    }
}
