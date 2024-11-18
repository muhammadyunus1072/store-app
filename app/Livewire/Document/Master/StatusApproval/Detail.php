<?php

namespace App\Livewire\Document\Master\StatusApproval;

use Exception;
use App\Helpers\General\Alert;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Document\Master\StatusApproval\StatusApprovalRepository;

class Detail extends Component
{
    public $objId;
    public $object;

    #[Validate('required', message: 'Nama Harus Diisi', onUpdate: false)]
    public $name;
    public $color = "#3d98fc";
    public $textColor = "#ffffff";
    public $isTriggerDone = false;
    public $isTriggerCancel = false;

    public function mount()
    {
        if ($this->objId) {
            $statusApproval = StatusApprovalRepository::find(Crypt::decrypt($this->objId));

            $this->name = $statusApproval->name;
            $this->color = $statusApproval->color;
            $this->textColor = $statusApproval->text_color;
            $this->isTriggerDone = $statusApproval->is_trigger_done;
            $this->isTriggerCancel = $statusApproval->is_trigger_cancel;
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('status_approval.edit', $this->objId);
        } else {
            $this->redirectRoute('status_approval.create');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('status_approval.index');
    }

    public function store()
    {
        $this->validate();

        $validatedData = [
            'name' => $this->name,
            'color' => $this->color,
            'text_color' => $this->textColor,
            'is_trigger_done' => $this->isTriggerDone,
            'is_trigger_cancel' => $this->isTriggerCancel,
        ];

        try {
            DB::beginTransaction();

            if ($this->objId) {
                $objId = Crypt::decrypt($this->objId);
                StatusApprovalRepository::update($objId, $validatedData);
            } else {
                $obj = StatusApprovalRepository::create($validatedData);
                $objId = $obj->id;
            }
            DB::commit();

            Alert::confirmation(
                $this,
                Alert::ICON_SUCCESS,
                "Berhasil",
                "Data Berhasil Diperbarui",
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
        return view('livewire.document.master.status-approval.detail');
    }
}
