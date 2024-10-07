<?php

namespace App\Models\Core\Company;

use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Core\Company\CompanyWarehouse;
use App\Models\Core\User\UserCompany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'name',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::deleted(function ($model) {
            $model->companyWarehouses()->delete();
        });
    }

    public function saveInfo($object, $prefix = "company")
    {
        $object[$prefix . "_name"] = $this->name;

        return $object;
    }

    public function isDeletable()
    {
        return true;
    }

    public function isEditable()
    {
        return true;
    }

    /*
    | RELATIONSHIP
    */
    public function userCompanies()
    {
        return $this->hasMany(UserCompany::class, 'company_id', 'id');
    }

    public function companyWarehouses()
    {
        return $this->hasMany(CompanyWarehouse::class, 'company_id', 'id');
    }
}
