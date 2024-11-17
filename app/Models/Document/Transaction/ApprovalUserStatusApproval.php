<?php

namespace App\Models\Document\Transaction;

use App\Models\Document\Master\StatusApproval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sis\TrackHistory\HasTrackHistory;

class ApprovalUserStatusApproval extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'approval_user_id',
        'status_approval_id',
    ];

    /*
    | RELATIONSHIP
    */
    public function approvalUser()
    {
        return $this->belongsTo(ApprovalUser::class, 'user_id', 'id');
    }

    public function statusApproval()
    {
        return $this->belongsTo(StatusApproval::class, 'status_approval_id', 'id');
    }
}
