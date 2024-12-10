<?php

namespace App\Models\Rsmh\GudangLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubBagian extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'GUDANGLOG.SUB_BAGIAN';

    protected $guarded = ['id'];

    // protected static function onBoot()
    // {
    // }

    public function isDeletable()
    {
        return true;
    }

    public function isEditable()
    {
        return true;
    }
}
