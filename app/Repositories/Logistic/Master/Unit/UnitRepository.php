<?php

namespace App\Repositories\Logistic\Master\Unit;

use Illuminate\Support\Facades\Crypt;
use App\Models\Logistic\Master\Unit\Unit;
use App\Repositories\MasterDataRepository;

class UnitRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return Unit::class;
    }

    public static function findWithDetails($id)
    {
        return Unit::with('unitDetails')->where('id', $id)->first();
    }

    public static function search($search)
    {
        $data = Unit::select('id', 'title')
            ->when($search, function ($query) use ($search) {
                $query->where('title', env('QUERY_LIKE'), '%' . $search . '%');
            })
            ->orderBy('title', 'asc')
            ->get()
            ->toArray();

        foreach ($data as $index => $item) {
            $data[$index]['id'] = Crypt::encrypt($item['id']);
            $data[$index]['text'] = $item['title'];
        }

        return json_encode($data);
    }

    public static function datatable()
    {
        return Unit::with('unitDetails');
    }
}
