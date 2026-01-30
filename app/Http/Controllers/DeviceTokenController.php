<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceTokenController extends Controller
{
    /**
     * Store a newly created device token in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'device_type' => ['required', 'string', Rule::in(['android', 'ios'])],
        ]);

        $user = $request->user();

        $user->deviceTokens()->updateOrCreate(
            [
                'token' => $validated['token'],
            ],
            [
                'device_type' => $validated['device_type'],
            ]
        );

        return response()->json(['message' => 'Device token stored successfully.'], 200);
    }
}
