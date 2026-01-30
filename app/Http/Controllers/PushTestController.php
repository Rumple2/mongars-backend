<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\NotificationManagerService;

class PushTestController extends Controller
{
    /**
     * Test push notification to a user (for frontend testing)
     * Route: POST /api/push-test
     * Body: { "user_id": "uuid", "title": "...", "body": "...", "data": { ... } }
     */
    public function sendTest(Request $request, NotificationManagerService $notificationManager)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'array',
        ]);

        $user = User::find($request->user_id);
        $title = $request->title;
        $body = $request->body;
        $data = $request->input('data', []);

        $notificationManager->send($user, 'test_push', $title, $body, $data);

        return response()->json(['success' => true, 'message' => 'Push notification sent (if device token exists).']);
    }
}
