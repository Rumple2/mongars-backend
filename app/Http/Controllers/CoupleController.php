<?php

namespace App\Http\Controllers;

use App\Models\Couple;
use App\Http\Requests\StoreCoupleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CoupleController extends Controller
{
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
