<?php

use App\Http\Controllers\Admin\AdminApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\Community\CommunityCategoryController;
use App\Http\Controllers\Community\CommunityCommentController;
use App\Http\Controllers\Community\CommunityPostController;
use App\Http\Controllers\CompanyApplicationController;
use App\Http\Controllers\Instructor\CourseController;
use App\Http\Controllers\Instructor\EnrollmentController;
use App\Http\Controllers\Instructor\LessonController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobPostingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizSubmissionController;
use App\Http\Controllers\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);



Route::middleware('auth:sanctum')->post('/select-role', [AuthController::class, 'selectRole']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/profile/student', [ProfileController::class, 'completeStudentProfile']);
    Route::post('/profile/instructor', [ProfileController::class, 'completeInstructorProfile']);
    Route::post('/profile/company', [ProfileController::class, 'completeCompanyProfile']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/pending-users', [AdminApprovalController::class, 'index']);
    Route::get('/pending-users/{id}', [AdminApprovalController::class, 'show']);
    Route::post('/approve-user/{id}', [AdminApprovalController::class, 'approve']);
    Route::post('/reject-user/{id}', [AdminApprovalController::class, 'reject']);
});


//✅ 1. Courses

Route::middleware(['auth:sanctum', 'student'])->group(function () {
    Route::get('/courses', [CourseController::class, 'publicIndex']);        // Public list
    Route::get('/courses/{id}', [CourseController::class, 'show']);
});
Route::middleware(['auth:sanctum', 'instructor'])->prefix('instructor')->group(function () {
    Route::get('/courses', [CourseController::class, 'index']);          // List my courses
    Route::post('/courses', [CourseController::class, 'store']);         // Create course
    Route::put('/courses/{id}', [CourseController::class, 'update']);    // Update course
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']); // Delete course
});


// ✅ 2. Lessons
// Instructor routes for managing lessons within their own courses
Route::middleware(['auth:sanctum', 'instructor'])->prefix('instructor')->group(function () {
    Route::get('/courses/{course_id}/lessons', [LessonController::class, 'index']);        // List lessons of a course
    Route::post('/courses/{course_id}/lessons', [LessonController::class, 'store']);       // Create lesson for a course
    Route::get('/courses/{course_id}/lessons/{lesson_id}', [LessonController::class, 'show']);  // Show lesson details
    Route::put('/courses/{course_id}/lessons/{lesson_id}', [LessonController::class, 'update']); // Update lesson
    Route::delete('/courses/{course_id}/lessons/{lesson_id}', [LessonController::class, 'destroy']); // Delete lesson
});

// Student routes to view lessons for courses they are enrolled in
Route::middleware(['auth:sanctum'])->prefix('student')->group(function () {
    Route::get('/courses/{course_id}/lessons', [LessonController::class, 'studentIndex']);  // List lessons in enrolled course
    Route::get('/courses/{course_id}/lessons/{lesson_id}', [LessonController::class, 'studentShow']);  // Show lesson details
});

// ✅ 3. Enrollments
Route::middleware(['auth:sanctum', 'student'])->prefix('student')->group(function () {
    Route::post('/enrollments', [EnrollmentController::class, 'store']);     // Enroll in course
    Route::get('/enrollments', [EnrollmentController::class, 'myEnrollments']); // View enrolled courses
});
Route::middleware(['auth:sanctum', 'instructor'])->prefix('instructor')->group(function () {
    Route::get('/courses/{course}/enrollments', [EnrollmentController::class, 'studentsInCourse']);
});

//✅ 4. Progress
Route::middleware(['auth:sanctum'])->prefix('student')->group(function () {
    Route::post('/progress', [ProgressController::class, 'store']); // student updates progress
    Route::get('/progress/course/{course_id}', [ProgressController::class, 'showStudentProgress']); // student views progress in course
});

Route::middleware(['auth:sanctum', 'instructor'])->prefix('instructor')->group(function () {
    Route::get('/courses/{course_id}/progress', [ProgressController::class, 'showEnrolledStudentsProgress']); // instructor views all students progress in a course
});

//✅ 5. Reviews

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/courses/{course}/reviews', [ReviewController::class, 'index']);
});



Route::middleware(['auth:sanctum', 'student'])->prefix('student')->group(function () {
    Route::post('/courses/{course_id}/reviews', [ReviewController::class, 'store']); // Create review
    Route::post('/reviews/{review}', [ReviewController::class, 'update']);         // Update own review
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);     // Delete own review
});

Route::middleware(['auth:sanctum', 'instructor'])->prefix('instructor')->group(function () {
    Route::get('/courses/{course}/reviews', [ReviewController::class, 'instructorIndex']); // View reviews on own course
});





//✅ 6. Quizzes
Route::middleware('auth:sanctum')->group(function () {

    // Instructor Routes
    Route::prefix('instructor')->middleware('instructor')->group(function () {

        // Quizzes
        Route::prefix('quizzes')->group(function () {
            Route::post('/', [QuizController::class, 'store']); //done
            Route::get('/', [QuizController::class, 'index']); //done
            Route::get('/{quiz}', [QuizController::class, 'show']); //done
            Route::match(['put', 'patch'], '/{quiz}', [QuizController::class, 'update']); //done
            Route::delete('/{quiz}', [QuizController::class, 'destroy']); //done

            // Questions nested under quizzes
            Route::prefix('/{quiz}/questions')->group(function () {
                Route::get('/', [QuestionController::class, 'index']); //done
                Route::post('/', [QuestionController::class, 'store']); //done
            });
        });

        // Questions (non-nested routes for update/show/delete)
        Route::prefix('questions')->group(function () {
            Route::get('/{question}', [QuestionController::class, 'show']); //done
            Route::match(['put', 'patch'], '/{question}', [QuestionController::class, 'update']); //done
            Route::delete('/{question}', [QuestionController::class, 'destroy']); //done
        });
    });

    // Student Routes
    Route::prefix('student')->middleware('student')->group(function () {
        Route::prefix('quizzes')->group(function () {
            Route::get('/lesson', [QuizController::class, 'listLessonQuizzes']); //done
            Route::get('/course', [QuizController::class, 'listCourseQuizzes']); //done
            Route::get('/{quiz}', [QuizController::class, 'ShowQuiz']);
            Route::post('/{quiz}/submit', [QuizSubmissionController::class, 'submit']); //done
        });

        Route::get('users/{user}/submissions', [QuizSubmissionController::class, 'userSubmissions']);
    });
});


//✅ 7. Certificates
Route::prefix('student')->middleware(['auth:sanctum'])->group(function () {
    Route::get('certificates', [CertificateController::class, 'index']);
    Route::get('certificates/{certificate}', [CertificateController::class, 'show']);
    Route::post('certificates/generate', [CertificateController::class, 'generate']);
});


Route::middleware(['auth:sanctum', 'company'])
    ->prefix('company')
    ->group(function () {
        Route::get('/job-postings', [JobPostingController::class, 'index']);
        Route::post('/job-postings', [JobPostingController::class, 'store']);
        Route::get('/job-postings/{job_posting}', [JobPostingController::class, 'show']);
        Route::put('/job-postings/{job_posting}', [JobPostingController::class, 'update']);
        Route::delete('/job-postings/{job_posting}', [JobPostingController::class, 'destroy']);
        Route::get('job-postings/{job}/recommended-students', [JobPostingController::class, 'ShowStudents']);
    });






Route::middleware(['auth:sanctum'])
    ->prefix('student')
    ->group(function () {
        Route::post('job-postings/{job}/apply', [JobApplicationController::class, 'apply']);
        Route::get('applications', [JobApplicationController::class, 'index']);
        Route::get('applications/{application}', [JobApplicationController::class, 'show']);
        Route::delete('applications/{application}', [JobApplicationController::class, 'cancel']);
    });



// Company routes
Route::middleware(['auth:sanctum', 'company'])->prefix('company')->group(function () {
    Route::get('job-postings/{job}/applications', [CompanyApplicationController::class, 'index']);
    Route::put('applications/{application}', [CompanyApplicationController::class, 'update']);
});














Route::middleware(['auth:sanctum'])->prefix('community')->group(function () {
    // Create a post
    Route::post('/posts', [CommunityPostController::class, 'store']);

    // List all posts
    Route::get('/posts', [CommunityPostController::class, 'index']);

    // View a single post
    Route::get('/posts/{post}', [CommunityPostController::class, 'show']);

    // Delete a post
    Route::delete('/posts/{post}', [CommunityPostController::class, 'destroy']);



    Route::post('/posts-create/{post}/comments', [CommunityCommentController::class, 'store']);
    Route::get('/posts/{post}/comments', [CommunityCommentController::class, 'index']);
    Route::delete('/comments/{comment}', [CommunityCommentController::class, 'destroy']);
});




Route::middleware(['auth:sanctum', 'admin'])->prefix('admin/community')->group(function () {
    Route::get('/categories', [CommunityCategoryController::class, 'index']);
    Route::post('/categories', [CommunityCategoryController::class, 'store']);
    Route::get('/categories/{category}', [CommunityCategoryController::class, 'show']);
    Route::put('/categories/{category}', [CommunityCategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CommunityCategoryController::class, 'destroy']);
});

Route::middleware(['auth:sanctum'])->prefix('community')->group(function () {
    Route::get('/categories', [CommunityCategoryController::class, 'userIndex']);
});









//ngrok http 8000



























// //✅ 9. Job Recommendations

// Route::middleware(['auth:sanctum', 'company'])->group(function () {
//     Route::post('/companies/{company}/job-recommendations', [JobRecommendationController::class, 'store']);
//     Route::get('/companies/{company}/job-recommendations', [JobRecommendationController::class, 'index']);
// });

// Route::middleware(['auth:sanctum', 'student'])->group(function () {
//     Route::get('/students/{student}/job-recommendations', [JobRecommendationController::class, 'forStudent']);
// });





// //✅ 10. Wishlist & Search

// Route::middleware(['auth:sanctum', 'student'])->group(function () {
//     Route::get('/wishlist', [WishlistController::class, 'index']);
//     Route::post('/wishlist/{course}', [WishlistController::class, 'add']);
//     Route::delete('/wishlist/{course}', [WishlistController::class, 'remove']);
// });

// Route::get('/courses/search', [CourseSearchController::class, 'search']);
// Route::get('/courses/filter', [CourseSearchController::class, 'filter']);
