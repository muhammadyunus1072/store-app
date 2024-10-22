<?php

namespace App\Traits\Document;

use App\Models\Document\Transaction\Approval;

trait HasApproval
{
    abstract public function approvalViewShow();
    abstract public function onApprovalDone();
    abstract public function onApprovalRevertDone();
    abstract public function onApprovalCanceled();
    abstract public function onApprovalRevertCancel();

    public function isHasApproval()
    {
        return !empty($this->approval);
    }

    public function isApprovalDone()
    {
        return $this->approval->isDone();
    }

    public function isApprovalCanceled()
    {
        return $this->approval->isCanceled();
    }

    public function approval()
    {
        return $this->hasOne(Approval::class, 'remarks_id', 'id')
            ->where('remarks_type', self::class);
    }
}
