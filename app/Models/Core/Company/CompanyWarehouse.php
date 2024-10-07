<?php

namespace App\Models\Core\Company;

use App\Models\Core\Company\Company;
use App\Models\Core\User\UserCompany;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyWarehouse extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'company_id',
        'warehouse_id',
    ];

    protected $guarded = ['id'];


    /*
    | RELATIONSHIP
    */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    public function userCompanies()
    {
        return $this->hasMany(UserCompany::class, 'company_id', 'company_id');
    }
}
