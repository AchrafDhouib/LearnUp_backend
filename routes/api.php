<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\Auth\AuthenticatedAdminSessionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\AuthenticatedStudentSessionController;
use App\Http\Controllers\Auth\AuthenticatedTeacherSessionController;
use App\Http\Controllers\Auth\EndSessionController;
use App\Http\Controllers\Auth\RegisteredStudentController;
use App\Http\Controllers\Auth\RegisteredTeacherController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\CoursController;
use App\Http\Controllers\DisciplineController;
use App\Http\Controllers\ExamsController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PassedExamsController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SpecialityController;
use App\Http\Controllers\UserAnswerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserGroupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user()->load('role');
});

Route::post('/auth/student/login', [AuthenticatedStudentSessionController::class, 'store']);
Route::post('/auth/teacher/login', [AuthenticatedTeacherSessionController::class, 'store']);
Route::post('/auth/admin/login', [AuthenticatedAdminSessionController::class, 'store']);
Route::post('/auth/users/login', [AuthenticatedAdminSessionController::class, 'store']);

Route::post('/auth/student/register', [RegisteredStudentController::class, 'store']);
Route::post('/auth/teacher/register', [RegisteredTeacherController::class, 'store']);
Route::get('/auth/users', [UserController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [EndSessionController::class, 'destroy']);
    Route::post('/auth/users/logout', [EndSessionController::class, 'destroy']);
    Route::patch('/users/{id}/activate', [UserController::class, 'activate'])->middleware('role:admin');
    Route::patch('/users/{id}/deactivate', [UserController::class, 'deactivate'])->middleware('role:admin');

});

Route::get('/desciplines', [DisciplineController::class, 'index']);
Route::get('/desciplines/{id}', [DisciplineController::class, 'show']);

Route::get('/specialities', [SpecialityController::class, 'index']);
Route::get('/specialities/{id}', [SpecialityController::class, 'show']);

Route::get('/courses', [CoursController::class, 'index']);
Route::get('/courses/{id}', [CoursController::class, 'show']);
Route::get('/courses/speciality/{specialityId}', [CoursController::class, 'getBySpeciality']);
Route::get('/courses/discipline/{disciplineId}', [CoursController::class, 'getByDiscipline']);

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/desciplines', [DisciplineController::class, 'store'])->middleware('role:admin');
    Route::put('/desciplines/{id}', [DisciplineController::class, 'update'])->middleware('role:admin');
    Route::delete('/desciplines/{id}', [DisciplineController::class, 'destroy'])->middleware('role:admin');

    Route::post('/specialities', [SpecialityController::class, 'store'])->middleware('role:admin');
    Route::put('/specialities/{id}', [SpecialityController::class, 'update'])->middleware('role:admin');
    Route::delete('/specialities/{id}', [SpecialityController::class, 'destroy'])->middleware('role:admin');

    Route::post('/courses', [CoursController::class, 'store'])->middleware('role:admin|teacher');
    Route::put('/courses/{id}', [CoursController::class, 'update'])->middleware('role:admin|teacher');
    Route::delete('/courses/{id}', [CoursController::class, 'destroy'])->middleware('role:admin|teacher');
    Route::patch('/courses/{id}/accept', [CoursController::class, 'accept'])->middleware('role:admin');
    Route::patch('/courses/{id}/reject' , [CoursController::class, 'reject'])->middleware('role:admin');

    Route::get('/lessons', [LessonController::class, 'index']);
    Route::get('/lessons/{id}', [LessonController::class, 'show']);
    Route::get('courses/{courseId}/lessons', [LessonController::class, 'getByCourse']);
    Route::post('courses/{courseId}/lessons', [LessonController::class, 'store'])->middleware('role:admin|teacher');
    Route::delete('courses/{courseId}/lessons/{id}', [LessonController::class, 'destroy'])->middleware('role:admin|teacher');
    Route::put('courses/{courseId}/lessons/{id}', [LessonController::class, 'update'])->middleware('role:admin|teacher');

    Route::get('/exams', [ExamsController::class, 'index']);
    Route::post('/exams', [ExamsController::class, 'store'])->middleware('role:admin|teacher');
    Route::get('/exams/{id}', [ExamsController::class, 'show']);
    Route::delete('/exams/{id}', [ExamsController::class, 'destroy'])->middleware('role:admin|teacher');
    Route::put('/exams/{id}', [ExamsController::class, 'update'])->middleware('role:admin|teacher');

    Route::get('/groups', [GroupController::class, 'index']);
    Route::post('/groups', [GroupController::class, 'store'])->middleware('role:admin|teacher');
    Route::get('/groups/{id}', [GroupController::class, 'show']);
    Route::delete('/groups/{id}', [GroupController::class, 'destroy'])->middleware('role:admin|teacher');
    Route::put('/groups/{id}', [GroupController::class, 'update'])->middleware('role:admin|teacher');
    Route::post('/groups/{groupId}/add-user', [GroupController::class, 'addUser']);
    Route::post('/groups/{groupId}/remove-user', [GroupController::class, 'removeUser']);

    Route::get('/users/{id}', [UserController::class, 'show'])->middleware('role:admin');
    Route::post('/users', [UserController::class, 'store'])->middleware('role:admin');
    Route::put('/users/{id}', [UserController::class, 'update'])->middleware('role:admin');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('role:admin');
    Route::post('/users/{userId}/join-group', [UserController::class, 'joinToGroup']);
    Route::post('/users/{userId}/leave-group', [UserController::class, 'leaveGroup']);

    Route::get('/user-groups', [UserGroupController::class, 'index']);
    Route::get('/user-groups/{userId}', [UserGroupController::class, 'show']);
    Route::post('/user-group/add', [UserGroupController::class, 'addUserToGroup']);
    Route::post('/user-group/remove', [UserGroupController::class, 'removeUserFromGroup']);

    Route::get('/passed-exams', [PassedExamsController::class,'index']);
    Route::get('/passed-exams/{id}', [PassedExamsController::class,'show']);
    Route::post('/passed-exams/{user_id}/{exam_id}', [PassedExamsController::class, 'store']);
    Route::put('/passed-exams/{id}/{score}', [PassedExamsController::class, 'update']);
    Route::delete('/passed-exams/{id}', [PassedExamsController::class,'destroy']);

    Route::get('/certifications', [CertificationController::class,'index']);
    Route::get('/certifications/{id}', [CertificationController::class,'show']);
    Route::post('/certifications', [CertificationController::class,'store']);
    Route::put('/certifications/{id}', [CertificationController::class,'update']);
    Route::delete('/certifications/{id}', [CertificationController::class,'destroy']);

    Route::get('/questions', [QuestionController::class,'index']);
    Route::get('/questions/{id}', [QuestionController::class,'show']);
    Route::post('/questions', [QuestionController::class,'store']);
    Route::put('/questions/{id}', [QuestionController::class,'update']);
    Route::delete('/questions/{id}', [QuestionController::class,'destroy']);

    
    Route::get('/answers', [AnswerController::class,'index']);
    Route::get('/answers/{id}', [AnswerController::class,'show']);
    Route::post('/answers', [AnswerController::class,'store']);
    Route::put('/answers/{id}', [AnswerController::class,'update']);
    Route::delete('/answers/{id}', [AnswerController::class,'destroy']);

    Route::get('/user-answers', [UserAnswerController::class,'index']);
    Route::get('/user-answers/{id}', [UserAnswerController::class,'show']);
    Route::post('/user-answers', [UserAnswerController::class,'store']);
    Route::put('/user-answers/{id}', [UserAnswerController::class,'update']);
    Route::delete('/user-answers/{id}', [UserAnswerController::class,'destroy']);

});
