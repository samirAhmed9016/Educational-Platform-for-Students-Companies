<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobPosting;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyApplicationController extends Controller
{
    public function index(JobPosting $job): JsonResponse
    {
        try {
            if ($job->company_id !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $applications = JobApplication::with('user')->where('job_posting_id', $job->id)->get();

            return response()->json(['success' => true, 'data' => $applications]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to load applications.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, JobApplication $application): JsonResponse
    {
        try {
            if ($application->jobPosting->company_id !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $request->validate([
                'status' => 'required|in:pending,accepted,rejected'
            ]);

            $application->status = $request->status;
            $application->save();

            return response()->json(['success' => true, 'message' => 'Application status updated.', 'data' => $application]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update status.', 'error' => $e->getMessage()], 500);
        }
    }
}
