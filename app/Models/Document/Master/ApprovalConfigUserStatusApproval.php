<?php

namespace App\Models\Document\Master;

use App\Models\Document\Master\ApprovalConfigUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalConfigUserStatusApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_config_user_id',
        'status_approval_id',
    ];
    
    /*
    | RELATIONSHIP
    */
    public function statusApproval()
    {
        return $this->belongsTo(StatusApproval::class, 'status_approval_id', 'id');
    }

    public function approvalConfigUser()
    {
        return $this->belongsTo(ApprovalConfigUser::class, 'approval_config_id', 'id');
    }
}
