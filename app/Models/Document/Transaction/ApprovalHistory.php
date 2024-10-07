<?php

namespace App\Models\Document\Transaction;

use App\Models\Core\User\User;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Document\Transaction\Approval;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Document\Master\StatusApproval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Repositories\Document\Transaction\ApprovalUserRepository;

class ApprovalHistory extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'approval_id',
        'user_id',
        'status_id',
        'position',
        'note',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $approval_user = ApprovalUserRepository::findByUser($model->approval_id, $model->user_id);
            $model->position = $approval_user->position;
        });

        self::created(function ($model) {
            $approval = $model->approval;
            $object = app($approval->remarks_type)->find($approval->remarks_id);
            $object->onStatusSubmit($model);
        });

        self::deleted(function ($model) {
            $approval = $model->approval;
            $object = app($approval->remarks_type)->find($approval->remarks_id);
            $object->onStatusCancel($model);
        });
    }
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
        return $this->belongsTo(StatusApproval::class, 'status_id', 'id');
    }
}
