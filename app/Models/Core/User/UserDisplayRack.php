<?php

namespace App\Models\Core\User;

use App\Models\Core\User\User;
use App\Models\Logistic\Master\DisplayRack\DisplayRack;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserDisplayRack extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'user_id',
        'display_rack_id',
    ];

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function displayRack()
    {
        return $this->belongsTo(DisplayRack::class, 'display_rack_id', 'id');
    }
}
