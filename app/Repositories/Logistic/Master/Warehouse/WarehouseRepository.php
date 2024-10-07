<?php

namespace App\Repositories\Logistic\Master\Warehouse;

use Illuminate\Support\Facades\Crypt;
use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Master\Warehouse\Warehouse;

class WarehouseRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return Warehouse::class;
    }

    public static function getByCompany($companyId)
    {
        return Warehouse::whereHas("companyWarehouses", function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->get();
    }

    public static function search($request)
    {
        $data = Warehouse::select('id', 'name as text')
            ->when($request->access, function ($query) use ($request) {
                $query->whereHas('companyWarehouses', function ($query) use ($request) {
                    $query->whereHas('userCompanies', function ($query) use ($request) {
                        $query->where('user_id', $request->user()->id);
                    });
                });
            })
            ->when($request->company_id, function ($query) use ($request) {
                $query->whereHas('companyWarehouses', function ($query) use ($request) {
                    $query->where('company_id', Crypt::decrypt($request->company_id));
                });
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', env('QUERY_LIKE'), "%$request->search%");
            })
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();

        foreach ($data as $index => $item) {
            $data[$index]['id'] = Crypt::encrypt($item['id']);
        }

        return json_encode($data);
    }

    public static function datatable()
    {
        return Warehouse::query();
    }
}
