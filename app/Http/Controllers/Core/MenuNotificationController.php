<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Repositories\Document\Transaction\ApprovalUserRepository;
use Illuminate\Support\Facades\Auth;

class MenuNotificationController extends Controller
{
    public function index()
    {
        $notification = [];

        $notification = array_merge($notification, $this->documentMenuNotification());

        return $notification;
    }

    private function documentMenuNotification()
    {
        $notification = [];

        $notification['menu_approval'] = ApprovalUserRepository::countMenuNotification(Auth::id());

        return $notification;
    }
}
