<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobPostingRequest;
use App\Http\Requests\UpdateJobPostingRequest;
use App\Models\JobPosting;
use App\Models\User;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobPostingController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $companyId = Auth::id();

            $jobPostings = JobPosting::where('company_id', $companyId)->latest()->get();

            return response()->json([
                'success' => true,
                'data' => $jobPostings
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch job postings.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreJobPostingRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $jobPosting = JobPosting::create([
                'company_id' => Auth::id(),
                'title' => $data['title'],
                'description' => $data['description'],
                'type' => $data['type'],
                'location' => $data['location'] ?? null,
                'deadline' => $data['deadline'] ?? null,
            ]);

            if (!empty($data['course_ids'])) {
                $jobPosting->courses()->sync($data['course_ids']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Job posting created successfully.',
                'data' => $jobPosting->load('courses')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create job posting.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(JobPosting $jobPosting): JsonResponse
    {
        try {
            if ($jobPosting->company_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this job posting.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $jobPosting->load('courses')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch job posting.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateJobPostingRequest $request, JobPosting $jobPosting): JsonResponse
    {
        try {
            if ($jobPosting->company_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this job posting.'
                ], 403);
            }

            $jobPosting->update($request->validated());

            if ($request->has('course_ids')) {
                $jobPosting->courses()->sync($request->input('course_ids'));
            }

            return response()->json([
                'success' => true,
                'message' => 'Job posting updated successfully.',
                'data' => $jobPosting->load('courses')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update job posting.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(JobPosting $jobPosting): JsonResponse
    {
        try {
            if ($jobPosting->company_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this job posting.'
                ], 403);
            }

            $jobPosting->delete();

            return response()->json([
                'success' => true,
                'message' => 'Job posting deleted successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete job posting.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function ShowStudents(JobPosting $job): JsonResponse
    {
        try {
            // Ensure the authenticated company owns this job
            if ($job->company_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this job posting.',
                ], 403);
            }

            // Get required course IDs for this job
            $requiredCourseIds = $job->courses()->pluck('courses.id')->toArray();

            if (empty($requiredCourseIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This job posting is not linked to any courses.',
                ], 400);
            }


            $students = User::where('role', 'student')
                ->whereHas('progress', function ($query) use ($requiredCourseIds) {
                    $query->whereIn('course_id', $requiredCourseIds)
                        ->where('is_completed', true);
                }, '=', count($requiredCourseIds))
                ->with(['studentProfile', 'certificates' => function ($q) use ($requiredCourseIds) {
                    $q->whereIn('course_id', $requiredCourseIds);
                }])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $students
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching recommended students.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
