<?php

namespace App\Repositories\Purchasing\Master\CategorySupplier;

use Illuminate\Support\Facades\Crypt;
use App\Repositories\MasterDataRepository;
use App\Models\Purchasing\Master\CategorySupplier\CategorySupplier;

class CategorySupplierRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return CategorySupplier::class;
    }

    public static function datatable()
    {
        return CategorySupplier::query();
    }

    public static function search($search)
    {
        $data = CategorySupplier::select('id', 'name as text')
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
}
