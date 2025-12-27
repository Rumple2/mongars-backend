<?php

namespace App\Http\Controllers;

use App\Models\SearchHistory;
use App\Http\Requests\StoreSearchHistoryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $histories = SearchHistory::with(['user', 'resultUser'])->paginate(20);
            return response()->json($histories);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSearchHistoryRequest $request)
    {
        try {
            $data = $request->validated();
            $data['id'] = Str::uuid();
            $data['searched_at'] = $data['searched_at'] ?? now();
            $history = SearchHistory::create($data);
            return response()->json(['success' => true, 'search_history' => $history->load(['user', 'resultUser'])], 201);
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
            $history = SearchHistory::with(['user', 'resultUser'])->find($id);
            if (!$history) {
                return response()->json(['error' => 'Search history not found'], 404);
            }
            return response()->json($history);
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
            $history = SearchHistory::find($id);
            if (!$history) {
                return response()->json(['error' => 'Search history not found'], 404);
            }
            $data = $request->all();
            $history->update($data);
            return response()->json(['success' => true, 'search_history' => $history->fresh(['user', 'resultUser'])]);
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
            $history = SearchHistory::find($id);
            if (!$history) {
                return response()->json(['error' => 'Search history not found'], 404);
            }
            $history->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
