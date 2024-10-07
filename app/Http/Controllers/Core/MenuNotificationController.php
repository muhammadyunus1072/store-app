<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Repositories\Transaction\TransactionRepository;

class MenuNotificationController extends Controller
{
    public function index()
    {
        $notification = [];

        // $notification['submenu_transaction'] = TransactionRepository::countMenuNotification();
        // $notification['menu_transaction'] = $notification['submenu_transaction'];

        return $notification;
    }
}
