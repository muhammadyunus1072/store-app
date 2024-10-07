<?php

namespace App\Repositories\Document\Master\StatusApproval;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\MasterDataRepository;
use App\Models\Document\Master\StatusApproval;

class StatusApprovalRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return StatusApproval::class;
    }
    public static function findByName($name)
    {
        return StatusApproval::where('name', $name)->first();
    }

    public static function getAll()
    {
        $data = StatusApproval::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();

        foreach ($data as $index => $item) {
            $data[$index]['id'] = Crypt::encrypt($item['id']);
        }

        return $data;
    }

    public static function datatable()
    {
        return StatusApproval::query();
    }
}
