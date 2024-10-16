<?php

namespace App\Models\Document\Transaction;

use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalUserHistory extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'approval_user_id',
        'note',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::created(function ($model) {
            $model->approvalUser->approval->onApprovalUserHistoryCreated($model);
        });

        self::deleted(function ($model) {
            $model->approvalUser->approval->onApprovalUserHistoryDeleted($model);
        });
    }

    /*
    | RELATIONSHIP
    */
    public function approvalUser()
    {
        return $this->belongsTo(ApprovalUser::class, 'approval_user_id', 'id');
    }
}
