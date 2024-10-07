<?php

namespace App\Repositories\Core\User;

use App\Models\Core\User\UserState;
use App\Repositories\MasterDataRepository;

class UserStateRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return UserState::class;
    }

    public static function createOrUpdate($userId, $state)
    {
        $userState = UserState::where('user_id', $userId)->first();

        if (empty($userState)) {
            self::create([
                'user_id' => $userId,
                'state' => json_encode($state),
            ]);
        } else {
            self::update(
                $userState->id,
                [
                    'state' => json_encode($state)
                ]
            );
        }
    }
}
