<?php

namespace App\Models\Sales\Master;

use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'name',
        'type',
        'amount',
        'code',
        'is_active',
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

    CONST TYPE_PERCENTAGE = 'percentage';
    CONST TYPE_FIXED = 'fixed';

    CONST TYPE_CHOICE = [
        self::TYPE_PERCENTAGE => 'Persentase',
        self::TYPE_FIXED => 'Nominal Tetap',
    ];

    public function saveInfo($object, $prefix = "payment_method_", $data = null)
    {
        if($data)
        {
            foreach($data as $item)
            {
                $object[$prefix . "".$item] = $this->$item;
            }
        }else{
            $object[$prefix . "name"] = $this->name;
            $object[$prefix . "type"] = $this->type;
            $object[$prefix . "amount"] = $this->amount;
            $object[$prefix . "code"] = $this->code;
        }

        return $object;
    }
}
