<?php

namespace App\Repositories\Finance\Master\Tax;

use App\Models\Finance\Master\Tax;
use App\Repositories\MasterDataRepository;
use Illuminate\Support\Facades\Crypt;

class TaxRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return Tax::class;
    }

    public static function firstPPN()
    {
        return Tax::where('type', Tax::TYPE_PPN)
            ->where('is_active', true)
            ->first();
    }

    public static function datatable()
    {
        return Tax::query();
    }

    public static function search($search)
    {
        $taxes = Tax::select('id', 'name', 'value')
            ->when($search, function ($query) use ($search) {
                $query->where('name', env('QUERY_LIKE'), '%' . $search . '%');
            })
            ->orderBy('name', 'asc')
            ->get();

        $data = [];
        foreach ($taxes as $index => $item) {
            $data[$index]['id'] = Crypt::encrypt($item->id);
            $data[$index]['text'] = $item->getText();
        }

        return json_encode($data);
    }
}
