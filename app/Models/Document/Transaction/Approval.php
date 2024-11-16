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

        self::deleted(function ($model) {
            foreach ($model->approvalUsers as $item) {
                $item->delete();
            }
            foreach ($model->approvalStatuses as $item) {
                $item->delete();
            }
        });
    }

    public function done($approvalStatus)
    {
        $this->done_at = $approvalStatus->created_at;
        $this->done_by_id = $approvalStatus->user_id;
        $this->save();

        if ($this->remarks) {
            $this->remarks->onApprovalDone();
        }
    }

    public function revertDone()
    {
        $this->done_at = null;
        $this->done_by_id = null;
        $this->save();

        if ($this->remarks) {
            $this->remarks->onApprovalRevertDone();
        }
    }

    public function cancel($approvalStatus)
    {
        $this->cancel_at = $approvalStatus->created_at;
        $this->cancel_by_id = $approvalStatus->user_id;
        $this->cancel_reason = $approvalStatus->note;
        $this->save();

        if ($this->remarks) {
            $this->remarks->onApprovalCanceled();
        }
    }

    public function revertCancel()
    {
        $this->cancel_at = null;
        $this->cancel_by_id = null;
        $this->cancel_reason = null;
        $this->save();

        if ($this->remarks) {
            $this->remarks->onApprovalRevertCancel();
        }
    }

    public function isDeletable()
    {
        return count($this->approvalStatuses) == 0;
    }

    public function isEditable()
    {
        return count($this->approvalStatuses) == 0;
    }

    public function isDone()
    {
        return !empty($this->done_at);
    }

    public function isCanceled()
    {
        return !empty($this->cancel_at);
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

    public function approvalStatuses()
    {
        return $this->hasMany(ApprovalStatus::class, 'approval_id', 'id');
    }

    public function remarks()
    {
        return $this->belongsTo($this->remarks_type, 'remarks_id', 'id');
    }
}
