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
            ->limit(10)
            ->get()
            ->toArray();

        foreach ($data as $index => $item) {
            $data[$index]['id'] = Crypt::encrypt($item['id']);
        }

        return json_encode($data);
    }

    public static function createOrUpdate($validatedData)
    {
        $obj = Warehouse::where('name', $validatedData['name'])
        ->where('id_sub', $validatedData['id_sub'])
        ->where('id_bagian', $validatedData['id_bagian'])
        ->where('id_direktorat', $validatedData['id_direktorat'])
        ->first();

        return empty($obj) ? Warehouse::create($validatedData) : $obj->update($validatedData);
    }

    public static function datatable()
    {
        return Warehouse::query();
    }
}
