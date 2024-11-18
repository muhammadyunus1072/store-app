<?php

namespace App\Models\Document\Master;

use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusApproval extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'name',
        'color',
        'text_color',
        'is_trigger_done',
        'is_trigger_cancel',
    ];

    protected $guarded = ['id'];

    public function saveInfo($object, $prefix = "status_approval")
    {
        $object[$prefix . "_name"] = $this->name;
        $object[$prefix . "_color"] = $this->color;
        $object[$prefix . "_text_color"] = $this->text_color;
        $object[$prefix . "_is_trigger_done"] = $this->is_trigger_done;
        $object[$prefix . "_is_trigger_cancel"] = $this->is_trigger_cancel;

        return $object;
    }

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
    public function approvalConfigUserStatusApprovals()
    {
        return $this->hasMany(ApprovalConfigUserStatusApproval::class, 'status_approval_id', 'id');
    }
}
