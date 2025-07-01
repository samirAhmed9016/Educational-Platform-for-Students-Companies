<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyProfileRequest;
use App\Http\Requests\InstructorProfileRequest;
use App\Http\Requests\StudentProfileRequest;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function completeStudentProfile(StudentProfileRequest $request)
    {
        $user = auth()->user();

        if ($user->role !== 'student') {
            return response()->json(['message' => 'Unauthorized role'], 403);
        }

        $data = $request->validated();

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('student_profiles', 'public');
            $user->profile_picture = $path;
            $user->save();
        }

        $user->studentProfile()->create($data);

        return response()->json([
            'message' => 'Student profile completed successfully.',
            'data' => $user,
        ]);
    }


    public function completeInstructorProfile(InstructorProfileRequest $request)
    {
        $user = auth()->user();

        if ($user->role !== 'instructor') {
            return response()->json(['message' => 'Unauthorized role'], 403);
        }

        $data = $request->validated();



        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('instructor_profiles', 'public');
            $user->profile_picture = $data['profile_image'];
            $user->save();
        }

        $user->instructorProfile()->create($data);

        return response()->json([
            'message' => 'Instructor profile submitted successfully. Awaiting approval by admin.',
            'data' => $user,
        ]);
    }

    public function completeCompanyProfile(CompanyProfileRequest $request)
    {
        $user = auth()->user();

        if ($user->role !== 'company') {
            return response()->json(['message' => 'Unauthorized role'], 403);
        }

        $data = $request->validated();

        if ($request->hasFile('company_logo')) {
            $data['company_logo'] = $request->file('company_logo')->store('company_logos', 'public');
            $user->profile_picture = $data['company_logo'];
            $user->save();
        }

        $user->companyProfile()->create($data);

        return response()->json([
            'message' => 'Company profile submitted successfully. Awaiting approval by admin.',
            'data' => $user,
        ]);
    }
}
