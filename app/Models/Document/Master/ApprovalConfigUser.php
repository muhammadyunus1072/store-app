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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function approvalConfig()
    {
        return $this->belongsTo(ApprovalConfig::class, 'approval_config_id', 'id');
    }
}
