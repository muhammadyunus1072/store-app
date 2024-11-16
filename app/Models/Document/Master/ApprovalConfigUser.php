<?php

namespace App\Models\Document\Master;

use App\Models\Core\User\User;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Document\Master\ApprovalConfig;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalConfigUser extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'approval_config_id',
        'user_id',
        'position',
    ];

    protected $guarded = ['id'];

    /*
    | RELATIONSHIP
    */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function approvalConfig()
    {
        return $this->belongsTo(ApprovalConfig::class, 'approval_config_id', 'id');
    }

    public function approvalConfigStatusApprovals()
    {
        return $this->hasMany(ApprovalConfigUserStatusApproval::class, 'approval_config_user_id', 'id');
    }

    public function statusApprovals()
    {
        return $this->belongsToMany(StatusApproval::class, 'approval_config_user_status_approvals', 'approval_config_user_id', 'status_approval_id')
            ->whereNull('approval_config_user_status_approvals.deleted_at');
    }
}
