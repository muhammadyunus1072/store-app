<?php

namespace App\Repositories\Core\User;

use App\Models\Core\User\UserDisplayRack;
use App\Repositories\MasterDataRepository;

class UserDisplayRackRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return UserDisplayRack::class;
    }

    public static function createIfNotExist($data)
    {
        $check = UserDisplayRack::where('user_id', $data['user_id'])
            ->where('display_rack_id', $data['display_rack_id'])
            ->first();

        if ($check) {
            return;
        }

        self::create($data);
    }

    public static function deleteExcept($userId, $ids)
    {
        $deletedData = UserDisplayRack::where('user_id', $userId)
            ->whereNotIn('display_rack_id', $ids)
            ->get();

        foreach ($deletedData as $item) {
            $item->delete();
        }
    }
}
