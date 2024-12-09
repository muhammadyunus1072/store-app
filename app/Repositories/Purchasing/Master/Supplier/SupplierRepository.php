<?php

namespace App\Repositories\Purchasing\Master\Supplier;

use Illuminate\Support\Facades\Crypt;
use App\Repositories\MasterDataRepository;
use App\Models\Purchasing\Master\Supplier\Supplier;

class SupplierRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return Supplier::class;
    }

    public static function createOrUpdate($validatedData)
    {
        $obj = Supplier::where('kode_simrs', $validatedData['kode_simrs'])->first();

        return empty($obj) ? Supplier::create($validatedData) : $obj->update($validatedData);
    }

    public static function search($search)
    {
        $data = Supplier::select('id', 'name as text')
            ->when($search, function ($query) use ($search) {
                $query->where('name', env('QUERY_LIKE'), '%' . $search . '%');
            })
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();

        foreach ($data as $index => $item) {
            $data[$index]['id'] = Crypt::encrypt($item['id']);
        }

        return json_encode($data);
    }

    public static function findWithDetails($id)
    {
        return Supplier::with('supplierCategories')->where('id', $id)->first();
    }

    public static function datatable()
    {
        return Supplier::with('supplierCategories');
    }
}
