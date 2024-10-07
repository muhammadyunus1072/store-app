<?php

namespace App\Models\Document\Transaction;

use App\Models\Core\User\User;
use Illuminate\Support\Facades\Auth;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Document\Transaction\ApprovalUser;
use App\Models\Document\Transaction\ApprovalHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Repositories\Document\Transaction\ApprovalRepository;
use App\Repositories\Document\Transaction\ApprovalUserRepository;
use App\Repositories\Document\Transaction\ApprovalHistoryRepository;
use App\Repositories\Document\Master\StatusApproval\StatusApprovalRepository;

class Approval extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'note',
        'is_sequentially',
        'remarks_id',
        'remarks_type',
        'config',
    ];

    protected $guarded = ['id'];

    public function isDeletable()
    {
        return true;
    }

    public function isEditable()
    {
        return true;
    }

    protected static function onBoot()
    {
        self::deleted(function ($model) {
            $model->approvalUsers()->delete();
            $model->approvalHistories()->delete();
        });
    }

    public function is_approved()
    {
        return ApprovalHistory::where('approval_id', $this->id)
        ->where('user_id', Auth::id())
        ->first();
    }

    public function is_sequentially()
    {
        if($this->is_sequentially)
        {
            $user = ApprovalUserRepository::findByUser($this->id, Auth::id());
            $previous_users = collect($this->approvalUsers)->where('position', '<', $user->position);
            
            foreach($previous_users as $previous_user)
            {
                $status_approved = StatusApprovalRepository::findByName('Setuju');
                return ApprovalHistoryRepository::findByUser($this->id, $previous_user['user_id'], $status_approved->id) ? true : false;
            }
            
            return true;
        }
        
        return true;
        
    }

    public function is_enabled()
    {
        return (!$this->is_approved() && $this->is_sequentially());
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function remarks_table()
    {
        return $this->belongsTo($this->remarks_type, 'remarks_id', 'id');
    }
    
    public function approvalUsers()
    {
        return $this->hasMany(ApprovalUser::class, 'approval_id', 'id');
    }

    public function approvalUser()
    {
        return $this->belongsTo(ApprovalUser::class, 'id', 'approval_id')->where('user_id', Auth::id());
    }

    public function approvalHistories()
    {
        return $this->hasMany(ApprovalHistory::class, 'approval_id', 'id');
    }
}
