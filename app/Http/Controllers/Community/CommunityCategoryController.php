<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\StoreCommunityCategoryRequest;
use App\Http\Requests\UpdateCommunityCategoryRequest;
use App\Models\CommunityCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Exception;

class CommunityCategoryController extends Controller
{
    // 🟢 List categories
    public function index(): JsonResponse
    {
        try {
            $this->authorizeAdmin();

            $categories = CommunityCategory::all();

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to fetch categories', $e);
        }
    }

    // 🟢 Store a new category
    public function store(StoreCommunityCategoryRequest $request): JsonResponse
    {
        try {
            $this->authorizeAdmin();

            $validated = $request->validated();
            $validated['slug'] = Str::slug($validated['name']);

            // Double check slug uniqueness
            if (CommunityCategory::where('slug', $validated['slug'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A category with this name already exists.'
                ], 409);
            }

            $category = CommunityCategory::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'data' => $category
            ], 201);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to create category', $e);
        }
    }

    // 🟢 Show a single category
    public function show(CommunityCategory $category): JsonResponse
    {
        try {
            $this->authorizeAdmin();

            return response()->json([
                'success' => true,
                'data' => $category,
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to retrieve category', $e);
        }
    }

    // 🟢 Update a category
    public function update(UpdateCommunityCategoryRequest $request, CommunityCategory $category): JsonResponse
    {
        try {
            $this->authorizeAdmin();

            $validated = $request->validated();
            if (isset($validated['name'])) {
                $validated['slug'] = Str::slug($validated['name']);

                // Ensure new slug is unique (except for this one)
                if (
                    CommunityCategory::where('slug', $validated['slug'])
                    ->where('id', '!=', $category->id)
                    ->exists()
                ) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A category with this name already exists.'
                    ], 409);
                }
            }

            $category->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'data' => $category
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update category', $e);
        }
    }

    // 🟢 Delete a category
    public function destroy(CommunityCategory $category): JsonResponse
    {
        try {
            $this->authorizeAdmin();

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.'
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to delete category', $e);
        }
    }

    // 🔐 Internal admin check
    private function authorizeAdmin(): void
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized. Only admins can perform this action.');
        }
    }

    // ❗ Reusable error response
    private function errorResponse(string $message, Exception $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => $e->getMessage()
        ], 500);
    }





    public function userIndex(): JsonResponse
    {
        try {
            $user = Auth::user();
            $role = $user->role;

            $categories = CommunityCategory::where('access_role', $role)
                ->orWhere('access_role', 'all')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
