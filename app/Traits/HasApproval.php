<?php

namespace App\Traits;

use Livewire\Attributes\On;
use App\Models\Document\Transaction\Approval;

trait HasApproval
{
    abstract public function onStatusSubmit($approvalHistory);
    abstract public function onStatusCancel($approvalHistory);
    abstract public function viewShow($approvalUser);

    public function approval()
    {
        return $this->hasOne(Approval::class, 'remarks_id', 'id')
            ->where('remarks_type', self::class);
    }

}
