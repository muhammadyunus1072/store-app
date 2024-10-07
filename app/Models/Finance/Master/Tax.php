<?php

namespace App\Models\Finance\Master;

use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tax extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'name',
        'type',
        'value',
        'is_active',
    ];

    protected $guarded = ['id'];

    const TYPE_PPN = 'PPN';
    const TYPE_PPH = 'PPH';
    const TYPE_CHOICE = [
        self::TYPE_PPN => self::TYPE_PPN,
        self::TYPE_PPH => self::TYPE_PPH,
    ];

    public function saveInfo($object, $prefix = "tax")
    {
        $object[$prefix . "_name"] = $this->name;
        $object[$prefix . "_type"] = $this->type;
        $object[$prefix . "_value"] = $this->value;
        $object[$prefix . "_is_active"] = $this->is_active;

        return $object;
    }

    public function getText()
    {
        return "{$this->name} ({$this->value}%)";
    }

    public function isDeletable()
    {
        return true;
    }

    public function isEditable()
    {
        return true;
    }
}
