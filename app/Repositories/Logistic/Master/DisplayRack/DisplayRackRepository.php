<?php

namespace App\Repositories\Logistic\Master\DisplayRack;

use Illuminate\Support\Facades\Crypt;
use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Master\DisplayRack\DisplayRack;

class DisplayRackRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return DisplayRack::class;
    }

    public static function search($request)
    {
        $data = DisplayRack::select('id', 'name as text')
            ->when($request->access, function ($query) use ($request) {
                $query->whereHas('companyDisplayRacks', function ($query) use ($request) {
                    $query->whereHas('userCompanies', function ($query) use ($request) {
                        $query->where('user_id', $request->user()->id);
                    });
                });
            })
            ->when($request->company_id, function ($query) use ($request) {
                $query->whereHas('companyDisplayRacks', function ($query) use ($request) {
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
        $obj = DisplayRack::where('name', $validatedData['name'])
            ->first();

        return empty($obj) ? DisplayRack::create($validatedData) : $obj->update($validatedData);
    }

    public static function datatable()
    {
        return DisplayRack::query();
    }
}
