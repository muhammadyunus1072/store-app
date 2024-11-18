<?php

namespace App\Models\Document\Transaction;

use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\General\NumberGenerator;
use App\Models\Core\User\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Document\Transaction\ApprovalUser;
use App\Repositories\Core\User\UserRepository;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Repositories\Document\Transaction\ApprovalUserRepository;
use Carbon\Carbon;

class Approval extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'number',
        'note',
        'done_at',
        'done_by',
        'cancel_at',
        'cancel_by',
        'is_sequentially',
        'is_done_when_all_submitted',
        'remarks_id',
        'remarks_type',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->number = NumberGenerator::generate(get_class($model), 'APP');
        });

        self::deleted(function ($model) {
            foreach ($model->approvalUsers as $item) {
                $item->delete();
            }
            foreach ($model->approvalStatuses as $item) {
                $item->delete();
            }
        });
    }

    public function isDeletable()
    {
        return count($this->approvalStatuses) == 0;
    }

    public function isEditable()
    {
        return count($this->approvalStatuses) == 0;
    }

    public function isDone()
    {
        return !empty($this->done_at);
    }

    public function isCanceled()
    {
        return !empty($this->cancel_at);
    }

    public function isAllUserSubmitStatus()
    {
        foreach ($this->approvalUsers as $approvalUser) {
            if (!$approvalUser->isStatusSubmitted()) {
                return false;
            }
        }

        return true;
    }

    public function checkCompleteness($lastSubmittedStatus)
    {
        if ($this->is_done_when_all_submitted) {
            $isAllUserSubmitStatus = $this->isAllUserSubmitStatus();

            if (empty($this->done_at) && empty($this->cancel_at) && $isAllUserSubmitStatus) {
                $this->done($lastSubmittedStatus);
            } else if (!empty($this->done_at) && !$isAllUserSubmitStatus) {
                $this->revertDone();
            }
        }
    }

    public function findCurrentApprovalUser($userId)
    {
        if ($this->is_sequentially) {
            $approvalUser = ApprovalUserRepository::findNextSubmission($this->id);
        } else {
            $approvalUser = ApprovalUserRepository::findNotSubmitted($this->id, $userId);
        }

        return $approvalUser && $approvalUser->user_id == $userId ? $approvalUser : null;
    }

    public function beautifyStatus($class = "")
    {
        if ($this->done_at) {
            $doneAt = Carbon::parse($this->done_at)->format('d F Y, H:i');
            return "<div class='badge badge-success {$class}'>Selesai ($doneAt)</div>";
        } else if ($this->cancel_at) {
            $cancelAt = Carbon::parse($this->done_at)->format('d F Y, H:i');
            return "<div class='badge badge-danger {$class}'>Batal ($cancelAt)</div>";
        } else {
            return "<div class='badge badge-primary {$class}'>Dalam Proses</div>";
        }
    }

    public function remarksUrlButton()
    {
        if (empty($this->remarks)) {
            return "";
        }

        $authUser = UserRepository::authenticatedUser();
        $remarksInfo = $this->remarks->approvalRemarksInfo();

        if (!$authUser->hasPermissionTo($remarksInfo['access'])) {
            return $remarksInfo['text'];
        }

        return "<a target='_blank' class='btn btn-info btn-sm' href='{$remarksInfo['url']}'>
            <i class='ki-solid ki-eye fs-1'></i>
            {$remarksInfo['text']}
        </a>";
    }

    /*
    | HANDLE: STATUS
    */
    public function handleStatusCreated($approvalStatus)
    {
        if ($approvalStatus->status_approval_is_trigger_done) {
            $this->done($approvalStatus);
        } elseif ($approvalStatus->status_approval_is_trigger_cancel) {
            $this->cancel($approvalStatus);
        }

        $this->checkCompleteness($approvalStatus);
    }

    public function handleStatusDeleted($approvalStatus)
    {
        if ($approvalStatus->status_approval_is_trigger_done) {
            $this->revertDone();
        } elseif ($approvalStatus->status_approval_is_trigger_cancel) {
            $this->revertCancel();
        }

        $this->checkCompleteness($approvalStatus);
    }

    public function done($approvalStatus)
    {
        $this->done_at = $approvalStatus->created_at;
        $this->done_by_id = $approvalStatus->user_id;
        $this->save();

        if ($this->remarks) {
            $this->remarks->onApprovalDone();
        }
    }

    public function revertDone()
    {
        $this->done_at = null;
        $this->done_by_id = null;
        $this->save();

        if ($this->remarks) {
            $this->remarks->onApprovalRevertDone();
        }
    }

    public function cancel($approvalStatus)
    {
        $this->cancel_at = $approvalStatus->created_at;
        $this->cancel_by_id = $approvalStatus->user_id;
        $this->cancel_reason = $approvalStatus->note;
        $this->save();

        if ($this->remarks) {
            $this->remarks->onApprovalCanceled();
        }
    }

    public function revertCancel()
    {
        $this->cancel_at = null;
        $this->cancel_by_id = null;
        $this->cancel_reason = null;
        $this->save();

        if ($this->remarks) {
            $this->remarks->onApprovalRevertCancel();
        }
    }

    /*
    | RELATIONSHIP
    */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function approvalUsers()
    {
        return $this->hasMany(ApprovalUser::class, 'approval_id', 'id');
    }

    public function approvalStatuses()
    {
        return $this->hasMany(ApprovalStatus::class, 'approval_id', 'id');
    }

    public function remarks()
    {
        return $this->belongsTo($this->remarks_type, 'remarks_id', 'id');
    }
}
