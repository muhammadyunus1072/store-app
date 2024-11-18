<?php

namespace App\Traits\Document;

use App\Models\Document\Transaction\Approval;
use App\Permissions\AccessDocument;
use App\Permissions\PermissionHelper;
use App\Repositories\Core\User\UserRepository;
use Illuminate\Support\Facades\Crypt;

trait HasApproval
{
    abstract public function approvalRemarksView();
    abstract public function approvalRemarksInfo();

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

    public function approvalUrlButton()
    {
        if (!$this->isHasApproval() || empty($this->approval)) {
            return "<div class='badge badge-secondary'>Tidak Terdapat Persetujuan</div>";
        }

        $html = "<div class='row'>";

        // APPROVAL INFO
        $html .= "<div class='col-auto'>";
        $html .= $this->approval->number . "<br>" . $this->approval->beautifyStatus();
        $html .= "</div>";

        $authUser = UserRepository::authenticatedUser();
        if ($authUser->hasPermissionTo(PermissionHelper::transform(AccessDocument::APPROVAL, PermissionHelper::TYPE_READ))) {
            $url = route('approval.show', Crypt::encrypt($this->approval->id));

            // APPROVAL VIEW BUTTON
            $html .= "<div class='col-auto'>";
            $html .= "<a class='btn btn-info btn-sm' href='{$url}'>
                <i class='ki-solid ki-eye fs-1'></i>
                Lihat Persetujuan
            </a>";
            $html .= "</div>";
        }

        $html .= "</div>";

        return $html;
    }

    /*
    | RELATIONSHIP
    */
    public function approval()
    {
        return $this->hasOne(Approval::class, 'remarks_id', 'id')
            ->where('remarks_type', self::class);
    }
}
