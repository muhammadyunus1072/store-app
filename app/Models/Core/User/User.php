<?php

namespace App\Models\Core\User;

use App\Models\Core\Company\Company;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use Sis\TrackHistory\HasTrackHistory;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, HasTrackHistory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function onBoot()
    {
        self::deleted(function ($model) {
            $model->userCompanies()->delete();
        });
    }

    /*
    | RELATIONSHIP
    */
    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'user_warehouses', 'user_id', 'warehouse_id')->whereNull('user_warehouses.deleted_at');
    }

    public function userWarehouses()
    {
        return $this->hasMany(UserWarehouse::class, 'user_id', 'id');
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'user_companies', 'user_id', 'company_id')->whereNull('user_companies.deleted_at');
    }

    public function userCompanies()
    {
        return $this->hasMany(UserCompany::class, 'user_id', 'id');
    }
}
