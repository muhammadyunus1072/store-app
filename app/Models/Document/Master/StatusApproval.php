<?php

namespace App\Models\Document\Master;

use App\Models\Document\Transaction\ApprovalUser;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusApproval extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'name'
    ];

    protected $guarded = ['id'];

    public function isDeletable()
    {
        return count($this->approvalUsers) == 0;
    }

    public function isEditable()
    {
        return true;
    }

    /*
    | RELATIONSHIP
    */
    public function approvalUsers()
    {
        return $this->belongsTo(ApprovalUser::class, 'status_approval_id', 'id');
    }
}
