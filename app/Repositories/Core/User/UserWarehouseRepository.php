<?php

namespace App\Repositories\Core\User;

use App\Models\Core\User\UserCompany;
use App\Models\Core\User\UserWarehouse;
use App\Repositories\MasterDataRepository;

class UserWarehouseRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return UserWarehouse::class;
    }

    public static function createIfNotExist($data)
    {
        $check = UserWarehouse::where('user_id', $data['user_id'])
            ->where('warehouse_id', $data['warehouse_id'])
            ->first();

        if ($check) {
            return;
        }

        self::create($data);
    }

    public static function deleteExcept($userId, $companyIds)
    {
        $deletedData = UserCompany::where('user_id', $userId)
            ->whereNotIn('warehouse_id', $companyIds)
            ->get();

        foreach ($deletedData as $item) {
            $item->delete();
        }
    }
}
