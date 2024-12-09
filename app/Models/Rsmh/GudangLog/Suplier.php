<?php

namespace App\Models\Rsmh\GudangLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Suplier extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'SUPLIER';

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
