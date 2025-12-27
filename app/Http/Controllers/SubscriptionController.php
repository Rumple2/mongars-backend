<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Http\Requests\StoreSubscriptionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $subscriptions = Subscription::with('user')->paginate(20);
            return response()->json($subscriptions);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubscriptionRequest $request)
    {
        try {
            $data = $request->validated();
            $data['id'] = Str::uuid();
            $data['is_active'] = $data['is_active'] ?? true;
            $data['started_at'] = $data['started_at'] ?? now();
            $subscription = Subscription::create($data);
            return response()->json(['success' => true, 'subscription' => $subscription->load('user')], 201);
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
            $subscription = Subscription::with('user')->find($id);
            if (!$subscription) {
                return response()->json(['error' => 'Subscription not found'], 404);
            }
            return response()->json($subscription);
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
            $subscription = Subscription::find($id);
            if (!$subscription) {
                return response()->json(['error' => 'Subscription not found'], 404);
            }
            $data = $request->all();
            $subscription->update($data);
            return response()->json(['success' => true, 'subscription' => $subscription->fresh('user')]);
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
            $subscription = Subscription::find($id);
            if (!$subscription) {
                return response()->json(['error' => 'Subscription not found'], 404);
            }
            $subscription->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
