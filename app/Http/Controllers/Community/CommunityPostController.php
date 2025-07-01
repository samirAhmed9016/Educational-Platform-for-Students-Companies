<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCommunityPostRequest;
use App\Models\CommunityPost;
use App\Models\CommunityCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;


class CommunityPostController extends Controller
{
    // Create a new community post
    public function store(StoreCommunityPostRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $category = CommunityCategory::findOrFail($request->category_id);

            // Role-based access check for category
            if (
                $category->access_role !== 'all' &&
                $category->access_role !== $user->role
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to post in this category.',
                ], 403);
            }

            $post = CommunityPost::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'title' => $request->title,
                'content' => $request->content,
                'visibility' => $request->visibility,
                'is_locked' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully.',
                'data' => $post,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create post.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // List all posts (with category filter if needed)
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();

            $query = CommunityPost::with([
                'user',
                'category',
                'comments' => function ($q) {
                    $q->with(['user', 'children.user']) // eager-load nested replies + authors
                        ->whereNull('parent_comment_id')   // only top-level comments
                        ->latest();
                }
            ])
                ->whereHas('category', function ($q) use ($user) {
                    $q->where('access_role', 'all')
                        ->orWhere('access_role', $user->role);
                })
                ->latest();

            if (request()->has('category_id')) {
                $query->where('category_id', request()->get('category_id'));
            }

            $posts = $query->get();

            return response()->json([
                'success' => true,
                'data' => $posts,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch posts.',
                'error' => $e->getMessage(),
            ], 500);
        }
        // try {
        //     $user = Auth::user();

        //     $query = CommunityPost::with('user', 'category')
        //         ->whereHas('category', function ($q) use ($user) {
        //             $q->where('access_role', 'all')
        //                 ->orWhere('access_role', $user->role);
        //         })
        //         ->latest();

        //     if (request()->has('category_id')) {
        //         $query->where('category_id', request()->get('category_id'));
        //     }

        //     $posts = $query->get();

        //     return response()->json([
        //         'success' => true,
        //         'data' => $posts,
        //     ]);
        // } catch (Exception $e) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Failed to fetch posts.',
        //         'error' => $e->getMessage(),
        //     ], 500);
        // }
    }

    // Show a single post with info
    public function show(CommunityPost $post): JsonResponse
    {
        try {
            $user = Auth::user();
            $category = $post->category;

            if (
                $category->access_role !== 'all' &&
                $category->access_role !== $user->role
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view this post.',
                ], 403);
            }

            $post->load(['user', 'category']);

            return response()->json([
                'success' => true,
                'data' => $post,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load post.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Delete a post
    public function destroy(CommunityPost $post): JsonResponse
    {
        try {
            $user = Auth::user();

            if ($user->id !== $post->user_id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this post.',
                ], 403);
            }

            $post->delete();

            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete post.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
