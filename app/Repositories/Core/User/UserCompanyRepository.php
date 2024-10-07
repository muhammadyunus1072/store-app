<?php

namespace App\Repositories\Core\User;

use App\Models\Core\User\UserCompany;
use App\Repositories\MasterDataRepository;

class UserCompanyRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return UserCompany::class;
    }

    public static function createIfNotExist($data)
    {
        $check = UserCompany::where('user_id', $data['user_id'])
            ->where('company_id', $data['company_id'])
            ->first();

        if ($check) {
            return;
        }

        self::create($data);
    }

    public static function deleteExcept($userId, $companyIds)
    {
        $deletedData = UserCompany::where('user_id', $userId)
            ->whereNotIn('company_id', $companyIds)
            ->get();

        foreach ($deletedData as $item) {
            $item->delete();
        }
    }
}
