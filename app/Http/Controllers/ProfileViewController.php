<?php

namespace App\Http\Controllers;

use App\Models\ProfileView;
use App\Http\Requests\StoreProfileViewRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProfileViewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $views = ProfileView::with(['viewer', 'viewedUser'])->paginate(20);
            return response()->json($views);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProfileViewRequest $request)
    {
        try {
            $data = $request->validated();
            $data['id'] = Str::uuid();
            $data['viewed_at'] = $data['viewed_at'] ?? now();
            $view = ProfileView::create($data);
            return response()->json(['success' => true, 'profile_view' => $view->load(['viewer', 'viewedUser'])], 201);
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
            $view = ProfileView::with(['viewer', 'viewedUser'])->find($id);
            if (!$view) {
                return response()->json(['error' => 'Profile view not found'], 404);
            }
            return response()->json($view);
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
            $view = ProfileView::find($id);
            if (!$view) {
                return response()->json(['error' => 'Profile view not found'], 404);
            }
            $data = $request->all();
            $view->update($data);
            return response()->json(['success' => true, 'profile_view' => $view->fresh(['viewer', 'viewedUser'])]);
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
            $view = ProfileView::find($id);
            if (!$view) {
                return response()->json(['error' => 'Profile view not found'], 404);
            }
            $view->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
