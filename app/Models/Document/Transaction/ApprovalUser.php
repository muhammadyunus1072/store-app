<?php

namespace App\Models\Document\Transaction;

use App\Models\Core\User\User;
use App\Models\Document\Master\StatusApproval;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Document\Transaction\Approval;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalUser extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'approval_id',
        'user_id',
        'position',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::deleted(function ($model) {
            foreach ($model->approvalUserStatuses as $item) {
                $item->delete();
            }

            if ($model->approvalStatus) {
                $model->approvalStatus->delete();
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

    public function approvalStatus()
    {
        return $this->hasOne(ApprovalStatus::class, 'approval_user_id', 'id');
    }

    public function approvalUserStatusApprovals()
    {
        return $this->hasMany(ApprovalUserStatusApproval::class, 'approval_user_id', 'id');
    }

    public function statusApprovals()
    {
        return $this->belongsToMany(StatusApproval::class, 'approval_user_status_approvals', 'approval_user_id', 'status_approval_id')
            ->whereNull('approval_user_status_approvals.deleted_at');
    }
}
