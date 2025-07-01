<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobPosting;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobApplicationController extends Controller
{
    public function apply(JobPosting $job): JsonResponse
    {
        try {
            $user = Auth::user();

            if (JobApplication::where('user_id', $user->id)->where('job_posting_id', $job->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already applied for this job.'
                ], 409);
            }

            $application = JobApplication::create([
                'user_id' => $user->id,
                'job_posting_id' => $job->id,
                'status' => 'pending',
                'applied_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully.',
                'data' => $application
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to apply.', 'error' => $e->getMessage()], 500);
        }
    }

    public function index(): JsonResponse
    {
        try {
            $applications = JobApplication::with('jobPosting')
                ->where('user_id', Auth::id())
                ->latest()
                ->get();

            return response()->json(['success' => true, 'data' => $applications]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch applications.', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(JobApplication $application): JsonResponse
    {
        try {
            if ($application->user_id !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            return response()->json(['success' => true, 'data' => $application->load('jobPosting')]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to load application.', 'error' => $e->getMessage()], 500);
        }
    }

    public function cancel(JobApplication $application): JsonResponse
    {
        try {
            if ($application->user_id !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $application->delete();

            return response()->json(['success' => true, 'message' => 'Application canceled successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to cancel application.', 'error' => $e->getMessage()], 500);
        }
    }
}
