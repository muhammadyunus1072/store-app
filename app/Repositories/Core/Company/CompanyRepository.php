<?php

namespace App\Repositories\Core\Company;

use App\Models\Core\Company\Company;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\MasterDataRepository;


class CompanyRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return Company::class;
    }

    public static function search($request)
    {
        $data = Company::select('id', 'name as text')            
            ->when($request->access, function ($query) use ($request) {
                $query->whereHas("userCompanies", function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                });
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', env('QUERY_LIKE'), '%' . $request->search . '%');
            })
            ->orderBy('name', 'asc')
            ->limit(10)
            ->get()
            ->toArray();

        foreach ($data as $index => $item) {
            $data[$index]['id'] = Crypt::encrypt($item['id']);
        }

        return json_encode($data);
    }

    public static function findWithDetails($id)
    {
        return Company::with('companyWarehouses')->where('id', $id)->first();
    }

    public static function datatable()
    {
        return Company::with('companyWarehouses');
    }
}
