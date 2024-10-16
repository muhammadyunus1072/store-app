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
        'status_approval_id',
        'position',
        'is_trigger_done',
        'is_can_cancel',

        'approval_config_user_id'
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::deleted(function ($model) {
            foreach ($model->approvalUserHistories as $item) {
                $item->delete();
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

    public function statusApproval()
    {
        return $this->belongsTo(StatusApproval::class, 'status_approval_id', 'id');
    }

    public function approval()
    {
        return $this->belongsTo(Approval::class, 'approval_id', 'id');
    }

    public function approvalUserHistories()
    {
        return $this->hasMany(ApprovalUserHistory::class, 'approval_user_id', 'id');
    }
}
