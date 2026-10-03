<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;


class AdminNotificationController extends BaseController
{

public function index()
{
    $notifications = auth()->user()->notifications()->latest()->paginate(20);
    auth()->user()->unreadNotifications->markAsRead();
    return view('admin.notifications', compact('notifications'));
}
    
    
    public function markRead(Request $request)
{
    if ($request->mark_all) {
        auth()->user()->unreadNotifications->markAsRead();
    } else {
        $notification = auth()->user()->notifications()->find($request->id);
        if ($notification) $notification->markAsRead();
    }

    return response()->json(['success' => true]);
}
}
