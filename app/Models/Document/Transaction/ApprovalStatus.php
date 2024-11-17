<?php

namespace App\Models\Document\Transaction;

use App\Models\Core\User\User;
use App\Models\Document\Master\StatusApproval;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalStatus extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'approval_id',
        'user_id',
        'status_approval_id',
        'approval_user_id',
        'note',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model = $model->statusApproval->saveInfo($model);
        });

        self::created(function ($model) {
            if ($model->status_approval_is_trigger_done) {
                $model->approval->done();
            } elseif ($model->status_approval_is_trigger_cancel) {
                $model->approval->cancel();
            }
        });

        self::updating(function ($model) {
            if ($model->status_approval_id != $model->getOriginal('status_approval_id')) {
                $model = $model->statusApproval->saveInfo($model);
            }
        });

        self::deleted(function ($model) {
            if ($model->status_approval_is_trigger_done) {
                $model->approval->revertDone();
            } elseif ($model->status_approval_is_trigger_cancel) {
                $model->approval->revertCancel();
            }
        });
    }

    /*
    | RELATIONSHIP
    */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function approval()
    {
        return $this->belongsTo(Approval::class, 'approval_id', 'id');
    }

    public function statusApproval()
    {
        return $this->belongsTo(StatusApproval::class, 'status_approval_id', 'id');
    }

    public function approvalUser()
    {
        return $this->belongsTo(ApprovalUser::class, 'approval_user_id', 'id');
    }
}
