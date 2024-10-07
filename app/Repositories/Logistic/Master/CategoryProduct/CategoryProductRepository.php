<?php

namespace App\Repositories\Logistic\Master\CategoryProduct;

use Illuminate\Support\Facades\Crypt;
use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Master\CategoryProduct\CategoryProduct;

class CategoryProductRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return CategoryProduct::class;
    }

    public static function search($search)
    {
        $data = CategoryProduct::select('id', 'name as text')
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

    public static function datatable()
    {
        return CategoryProduct::query();
    }
}
