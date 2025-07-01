<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCommunityCommentRequest;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CommunityCommentController extends Controller
{

    /**
     * Store a new comment or reply on a community post.
     */
    public function store(Request $request, CommunityPost $post): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Not authenticated'], 401);
            }

            // Access check: must match the category's access_role
            $category = $post->category;
            if (
                $category->access_role !== 'all' &&
                $category->access_role !== $user->role
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to comment in this category.'
                ], 403);
            }

            // Validate input manually
            $validated = $request->validate([
                'content' => 'required|string|max:3000',
                'parent_comment_id' => 'nullable|integer|exists:community_comments,id'
            ]);

            // Optional: Check if parent comment exists and belongs to the same post
            if (isset($validated['parent_comment_id'])) {
                $parent = CommunityComment::where('id', $validated['parent_comment_id'])
                    ->where('post_id', $post->id)
                    ->first();

                if (!$parent) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid parent comment.'
                    ], 400);
                }
            }

            $comment = CommunityComment::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
                'parent_comment_id' => $validated['parent_comment_id'] ?? null,
                'content' => $validated['content'],
            ]);


            return response()->json([
                'success' => true,
                'message' => 'Comment posted successfully.',
                'data' => $comment->load(['user', 'children.user'])
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to post comment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all top-level comments (and their replies) on a post.
     */
    public function index(CommunityPost $post): JsonResponse
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
                    'message' => 'You are not allowed to view comments in this category.'
                ], 403);
            }

            $comments = CommunityComment::with([
                'user:id,name,role',
                'children.user:id,name,role'
            ])
                ->where('post_id', $post->id)
                ->whereNull('parent_comment_id')
                ->where('is_hidden', false)

                ->get();

            return response()->json([
                'success' => true,
                'data' => $comments
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load comments.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete (soft delete / hide) a comment.
     */
    public function destroy(CommunityComment $comment): JsonResponse
    {
        try {
            $user = Auth::user();

            // Only the comment owner or an admin can delete
            if ($user->id !== $comment->user_id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to delete this comment.'
                ], 403);
            }

            $comment->is_hidden = true;
            $comment->save();

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete comment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
