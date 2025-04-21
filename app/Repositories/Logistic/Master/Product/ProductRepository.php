<?php

namespace App\Repositories\Logistic\Master\Product;

use Illuminate\Support\Facades\Crypt;
use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Master\Product\Product;

class ProductRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return Product::class;
    }

    public static function findWithDetails($id)
    {
        return Product::with('unit', 'productCategories', 'productCategories.categoryProduct')->where('id', $id)->first();
    }

    public static function search($request)
    {
        $data = Product::select(
            'id',
            'name',
        )
            ->when($request->product_stock, function ($query) {
                $query->where('type', Product::TYPE_PRODUCT_WITH_STOCK);
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
            $data[$index]['text'] = "{$item['name']}";
        }

        return json_encode($data);
    }

    public static function datatable()
    {
        return Product::with('unit', 'productCategories', 'productCategories.categoryProduct');
    }
}
