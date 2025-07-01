<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateController extends Controller
{

    public function generate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $course = Course::findOrFail($request->course_id);

        $courseCompleted = CourseUser::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereNotNull('completed_at')
            ->exists();

        if (! $courseCompleted) {
            return response()->json(['message' => 'You have not completed this course.'], 403);
        }

        $existing = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Certificate already exists.',
                'certificate_url' => $existing->certificate_url,
            ]);
        }

        //PDF
        $pdf = Pdf::loadView('certificates.template', [
            'user' => $user,
            'course' => $course,
            'issued_at' => now(),
        ]);

        $filename = 'certificates/' . uniqid('certificate_') . '.pdf';
        Storage::put("public/{$filename}", $pdf->output());

        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'issued_at' => now(),
            'certificate_url' => Storage::url("public/{$filename}"),
        ]);

        return response()->json([
            'message' => 'Certificate generated successfully.',
            'certificate_url' => $certificate->certificate_url,
        ]);
    }

    public function index(): JsonResponse
    {
        $user = Auth::user();

        $certificates = Certificate::with('course')
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'certificates' => $certificates
        ]);
    }

    public function show(Certificate $certificate): JsonResponse
    {
        $user = Auth::user();

        if ($certificate->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'certificate' => $certificate->load('course')
        ]);
    }
}
