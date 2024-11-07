<?php

namespace App\Models\Document\Transaction;

use Carbon\Carbon;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\General\NumberGenerator;
use App\Models\Core\User\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Document\Transaction\ApprovalUser;
use App\Models\Document\Transaction\ApprovalHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Repositories\Document\Transaction\ApprovalRepository;

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
        'remarks_id',
        'remarks_type',

        'approval_config_id',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->number = NumberGenerator::generate(get_class($model), 'APP');
        });

        self::updating(function ($model) {
            if (!empty($model->remarks_id) && !empty($model->remarks_type) && !empty($model->remarks)) {
                if ($model->done_at != $model->getOriginal('done_at')) {
                    if (empty($model->done_at)) {
                        $model->remarks->onApprovalRevertDone();
                    } else {
                        $model->remarks->onApprovalDone();
                    }
                }

                if ($model->cancel_at != $model->getOriginal('cancel_at')) {
                    if (empty($model->cancel_at)) {
                        $model->remarks->onApprovalRevertCancel();
                    } else {
                        $model->remarks->onApprovalCanceled();
                    }
                }
            }
        });

        self::deleted(function ($model) {
            foreach ($model->approvalUsers as $item) {
                $item->delete();
            }
        });
    }

    public function onApprovalUserHistoryCreated($approvalUserHistory)
    {
        if ($approvalUserHistory->approvalUser->is_trigger_done || $this->isAllApproved()) {
            ApprovalRepository::update($this->id, [
                'done_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'done_by' => $approvalUserHistory->approvalUser->user_id,
            ]);
        }
    }

    public function onApprovalUserHistoryDeleted($approvalUserHistory)
    {
        if ($approvalUserHistory->approvalUser->is_trigger_done || !$this->isAllApproved()) {
            ApprovalRepository::update($this->id, [
                'done_at' => null,
                'done_by' => null,
            ]);
        }
    }

    public function isDeletable()
    {
        return count($this->approvalUserHistories) == 0;
    }

    public function isEditable()
    {
        return count($this->approvalUserHistories) == 0;
    }

    public function isDone()
    {
        return !empty($this->done_at);
    }

    public function isCanceled()
    {
        return !empty($this->cancel_at);
    }

    public function is_enabled()
    {
        return true;
    }

    public function isAllApproved()
    {
        return $this->approvalUsers()->count() == $this->approvalUserHistories()->count();
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

    public function approvalUserHistories()
    {
        return $this->belongsToMany(ApprovalUserHistory::class, 'approval_users', 'approval_id', 'user_id')->whereNull('approval_users.deleted_at');
    }

    public function remarks()
    {
        return $this->belongsTo($this->remarks_type, 'remarks_id', 'id');
    }
}
