<?php

namespace App\Helpers\Core;

use App\Repositories\Core\User\UserStateRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class UserStateHandler
{
    public $state;
    public $companies = [];
    public $warehouses = [];

    public function __construct()
    {
        $user = Auth::user();

        // Handle Company Options
        foreach ($user->companies as $index => $company) {
            $this->companies[$index] = [
                'id' => Crypt::encrypt($company->id),
                'name' => $company->name,
            ];
        }

        // Handle Warehouse Options
        foreach ($user->warehouses as $index => $warehouse) {
            $this->warehouses[$index] = [
                'id' => Crypt::encrypt($warehouse->id),
                'name' => $warehouse->name,
            ];
        }

        // Handle User State
        $userState = UserStateRepository::findBy(whereClause: [['user_id', $user->id]]);

        if (empty($userState)) {
            $this->state = [
                'company_id' => count($this->companies) > 0 ? $this->companies[0]['id'] : null,
                'warehouse_id' => count($this->warehouses) > 0 ? $this->warehouses[0]['id'] : null,
            ];

            // Create State
            UserStateRepository::create([
                'user_id' => Auth::id(),
                'state' => json_encode([
                    'company_id' => $this->state['company_id'] ? Crypt::decrypt($this->state['company_id']) : null,
                    'warehouse' => $this->state['warehouse_id'] ? Crypt::decrypt($this->state['warehouse_id']) : null,
                ]),
            ]);
        } else {
            $this->state = json_decode($userState->state, true);

            // Check Validity Old State : Company
            $isFound = false;
            foreach ($user->companies as $index => $company) {
                if ($company->id == $this->state['company_id']) {
                    $isFound = true;
                    $this->state['company_id'] = $this->companies[$index]['id']; // Set Encrypted ID
                    break;
                }
            }
            if (!$isFound && count($this->companies) > 0) {
                $this->state['company_id'] = $this->companies[0]['id'];
            }

            // Check Validity Old State : Warehouse
            $isFound = false;
            foreach ($user->warehouses as $index => $warehouse) {
                if ($warehouse->id == $this->state['warehouse_id']) {
                    $isFound = true;
                    $this->state['warehouse_id'] = $this->warehouses[$index]['id']; // Set Encrypted ID
                    break;
                }
            }
            if (!$isFound && count($this->warehouses) > 0) {
                $this->state['warehouse_id'] = $this->warehouses[0]['id'];
            }

            // Update State
            UserStateRepository::update($userState->id, [
                'state' => json_encode([
                    'company_id' => $this->state['company_id'] ? Crypt::decrypt($this->state['company_id']) : null,
                    'warehouse' => $this->state['warehouse_id'] ? Crypt::decrypt($this->state['warehouse_id']) : null,
                ]),
            ]);
        }
    }

    public static function set($state)
    {
        UserStateRepository::createOrUpdate(
            userId: Auth::id(),
            state: $state
        );
    }

    public static function get($key = null)
    {
        $handler = app(self::class);

        if ($key == 'warehouses') {
            return $handler->warehouses;
        } else if ($key == 'companies') {
            return $handler->companies;
        } else if ($key != null) {
            return isset($handler->state[$key]) ? $handler->state[$key] : null;
        } else {
            return [
                'user_id' => Auth::id(),
                'companies' => $handler->companies,
                'warehouses' => $handler->warehouses,
                'company_id' => $handler->state['company_id'],
                'warehouse_id' => $handler->state['warehouse_id'],
            ];
        }
    }
}
