<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserApprovedMail;
use App\Mail\UserRejectedMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminApprovalController extends Controller
{
    public function index()
    {
        $pendingUsers = User::whereIn('role', ['instructor', 'company'])
            ->where('is_approved', false)
            ->get();

        return response()->json($pendingUsers);
    }


    public function show($id)
    {
        $user = User::findOrFail($id);

        if (!in_array($user->role, ['instructor', 'company'])) {
            return response()->json(['message' => 'User is not eligible for approval.'], 400);
        }

        if ($user->role === 'instructor') {
            $profile = $user->instructorProfile;
        } elseif ($user->role === 'company') {
            $profile = $user->companyProfile;
        }

        return response()->json([
            'user' => $user,

        ]);
    }

    public function approve($id)
    {
        $user = User::findOrFail($id);

        if ($user->is_approved) {
            return response()->json(['message' => 'User is already approved.'], 400);
        }

        $user->update(['is_approved' => true]);
        Mail::to($user->email)->send(new UserApprovedMail($user));


        return response()->json(['message' => 'User approved successfully.']);
    }

    public function reject($id)
    {
        $user = User::findOrFail($id);

        $user->update(['status' => 'banned']);

        Mail::to($user->email)->send(new UserRejectedMail($user));


        return response()->json(['message' => 'User has been rejected and banned.']);
    }
}
