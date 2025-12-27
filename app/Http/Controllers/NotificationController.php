<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Http\Requests\StoreNotificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $notifications = Notification::with('user')->paginate(20);
            return response()->json($notifications);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNotificationRequest $request)
    {
        try {
            $data = $request->validated();
            $data['id'] = Str::uuid();
            $data['read'] = $data['read'] ?? false;
            $notification = Notification::create($data);
            return response()->json(['success' => true, 'notification' => $notification->load('user')], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $notification = Notification::with('user')->find($id);
            if (!$notification) {
                return response()->json(['error' => 'Notification not found'], 404);
            }
            return response()->json($notification);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $notification = Notification::find($id);
            if (!$notification) {
                return response()->json(['error' => 'Notification not found'], 404);
            }
            $data = $request->all();
            $notification->update($data);
            return response()->json(['success' => true, 'notification' => $notification->fresh('user')]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $notification = Notification::find($id);
            if (!$notification) {
                return response()->json(['error' => 'Notification not found'], 404);
            }
            $notification->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
        /**
     * Get the count of unread notifications for the authenticated user.
     */
    public function unreadCount(Request $request)
    {
        try {
            $user = $request->user();
            $count = $user->notifications()->where('read', false)->count();
            return response()->json(['unread_count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
