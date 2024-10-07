<?php

namespace App\Helpers\Core;

use App\Repositories\Core\User\UserStateRepository;
use Illuminate\Support\Facades\Auth;

class UserStateHelper
{
    public static function save($state)
    {
        UserStateRepository::createOrUpdate(
            userId: Auth::id(),
            state: $state
        );
    }

    public static function get()
    {
        $userId = Auth::id();

        $userState = UserStateRepository::findBy(whereClause: [['user_id', $userId]]);
        $userCompanyId = null;
        $userWarehouseId = null;
        if (!empty($userState)) {
            $state = json_decode($userState->state, true);

            if (!empty($state) && isset($state['company_id'])) {
                $userCompanyId = $state['company_id'];
            }

            if (!empty($state) && isset($state['warehouse_id'])) {
                $userWarehouseId = $state['warehouse_id'];
            }
        }

        return [
            'user_id' => $userId,
            'company_id' => $userCompanyId,
            'warehouse_id' => $userWarehouseId,
        ];
    }
}
