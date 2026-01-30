<?php

namespace App\Http\Controllers;

use App\Models\Couple;
use App\Http\Requests\StoreCoupleRequest;
use App\Notifications\PartnerBrokeUpNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CoupleController extends Controller
{
    /**
     * Get the couple for the authenticated user.
     */
    public function myCouple(Request $request)
    {
        try {
            $user = $request->user();
            $couple = Couple::where('is_active', true)
                ->where(function ($query) use ($user) {
                    $query->where('user1_id', $user->id)
                          ->orWhere('user2_id', $user->id);
                })
                ->with(['user1', 'user2'])
                ->first();

            if (!$couple) {
                return response()->json(null, 200); // Return null if not in a couple, which is not an error.
            }

            return response()->json($couple);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching couple data.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * End a relationship.
     */
    public function breakUp(Request $request)
    {
        try {
            $user = $request->user();
            $couple = Couple::where('is_active', true)
                ->where(function ($query) use ($user) {
                    $query->where('user1_id', $user->id)
                          ->orWhere('user2_id', $user->id);
                })
                ->first();

            if (!$couple) {
                return response()->json(['error' => 'You are not in an active relationship.'], 404);
            }

            // Identify the partner
            $partner = ($couple->user1_id == $user->id) ? $couple->user2 : $couple->user1;

            // Deactivate the couple
            $couple->is_active = false;
            $couple->ended_at = now();
            $couple->save();

            // Update users' status and couple_id
            $user1 = $couple->user1;
            $user2 = $couple->user2;

            if ($user1) {
                $user1->status = 'SINGLE'; // Assuming 'SINGLE' is the correct status for single users
                $user1->couple_id = null;
                $user1->save();
            }

            if ($user2) {
                $user2->status = 'SINGLE'; // Assuming 'SINGLE' is the correct status for single users
                $user2->couple_id = null;
                $user2->save();
            }

            // Find and update the CoupleRequest that led to this couple
            // We assume there was an 'ACCEPTED' request between these two users
            \App\Models\CoupleRequest::where(function ($query) use ($user1, $user2) {
                $query->where('sender_id', $user1->id)
                      ->where('receiver_id', $user2->id);
            })->orWhere(function ($query) use ($user1, $user2) {
                $query->where('sender_id', $user2->id)
                      ->where('receiver_id', $user1->id);
            })->where('status', 'ACCEPTED')
              ->update(['status' => 'ENDED', 'responded_at' => now()]);

            // Notify the partner
            if ($partner) {
                $partner->notify(new PartnerBrokeUpNotification($user->name));
            }

            return response()->json(['success' => true, 'message' => 'The relationship has been ended.']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred during the breakup.', 'details' => $e->getMessage()], 500);
        }
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $couples = Couple::with(['user1', 'user2'])->paginate(20);
            return response()->json($couples);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCoupleRequest $request)
    {
        try {
            $data = $request->validated();
            $data['id'] = Str::uuid();
            $data['is_active'] = $data['is_active'] ?? true;
            $couple = Couple::create($data);
            return response()->json(['success' => true, 'couple' => $couple->load(['user1', 'user2'])], 201);
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
            $couple = Couple::with(['user1', 'user2'])->find($id);
            if (!$couple) {
                return response()->json(['error' => 'Couple not found'], 404);
            }
            return response()->json($couple);
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
            $couple = Couple::find($id);
            if (!$couple) {
                return response()->json(['error' => 'Couple not found'], 404);
            }
            $data = $request->all();
            $couple->update($data);
            return response()->json(['success' => true, 'couple' => $couple->fresh(['user1', 'user2'])]);
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
            $couple = Couple::find($id);
            if (!$couple) {
                return response()->json(['error' => 'Couple not found'], 404);
            }
            $couple->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
