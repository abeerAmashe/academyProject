<?php

use App\Http\Controllers\AcademyAdmin\AcademyAdminCourseController;
use App\Http\Controllers\AcademyAdmin\AcademyAdminExamController;
use App\Http\Controllers\AcademyAdmin\AcademyAdminProfilecontroller;
use App\Http\Controllers\AcademyAdmin\AcademyAdminStudentController;
use App\Http\Controllers\AcademyAdmin\AcademyAdminTeacherController;
use App\Http\Controllers\AcademyAdmin\AuthAcademyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\Student\AuthStudentController;
use App\Http\Controllers\Student\ProfileStudentController;
use App\Http\Controllers\Student\CourseStudentController;
use App\Http\Controllers\Student\AcademyStudentController;
use App\Http\Controllers\Student\LessonStudentController;
use App\Http\Controllers\Student\GroupStudentController;
use App\Http\Controllers\Teacher\AuthTeacherController;
use App\Http\Controllers\Teacher\ProfileTeacherController;
use App\Http\Controllers\Teacher\HomeTeacherController;
use App\Http\Controllers\Teacher\CourseTeacherController;
use App\Http\Controllers\Teacher\InstituesTeacherController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Student\NotificationController;
use App\Http\Controllers\Student\OfferStudentController;
use App\Http\Controllers\Student\RateController;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\SuperAdmin\RequestMangeController;
use App\Models\OfferStudent;

// taked
Route::post('student/register', [AuthStudentController::class, 'register']);
// taked
Route::post('teacher/register', [AuthTeacherController::class, 'register']);
//  taked
Route::post('academy-admin/register', [AuthAcademyController::class, 'register']);
//taked
Route::post('/login', [LoginController::class, 'login']);


Route::group(['prefix' => 'student', 'middleware' => ['auth:sanctum', 'student']], function () {

    Route::group(['prefix' => 'profile'], function () {
        //done
        Route::get('/', [ProfileStudentController::class, 'show']);
        //done  
        Route::post('/', [ProfileStudentController::class, 'update']);
        //done  
        Route::post('/change-password', [ProfileStudentController::class, 'changePassword']);
    });

    Route::group(['prefix' => 'courses'], function () {
        Route::post('join_to_course/{course}', [CourseStudentController::class, 'join_to_course']);
        //done
        Route::get('showStudentCourse', [CourseStudentController::class, 'showCourses']);
        Route::get('showCourse/{id}', [CourseStudentController::class, 'showCourse']);
        //done     
        Route::get('/enrolled-courses', [CourseStudentController::class, 'enrolledCourses']);
        //image    
        Route::get('certificate', [ProfileStudentController::class, 'certificats']);
        //done
        Route::get('showExam/{course}', [CourseStudentController::class, 'showExam']);
        //done    
        Route::post('solve-exam/{course}', [CourseStudentController::class, 'solveExam']);
        Route::group(['prefix' => '{course}/lessons'], function () {
            //done    
            Route::get('/', [LessonStudentController::class, 'lessons']);
            //done    
            Route::get('/{lesson}', [LessonStudentController::class, 'show']);
        });
    });

    Route::group(['prefix' => 'academies'], function () {
        //done     
        Route::get('/', [AcademyStudentController::class, 'index']);
        //done    
        Route::post('join/{academy}', [AcademyStudentController::class, 'joinToAcademy']);
        //done     
        Route::get('show/{academy}', [AcademyStudentController::class, 'academy']);
        //done     
        Route::get('show-request', [AcademyStudentController::class, 'showRequest']);
        //done
        Route::get('cancel-request/{academy}', [AcademyStudentController::class, 'delete']);
        //done    
        Route::post('feedback/{academy}', [AcademyStudentController::class, 'addFeedBack']);
    });
    Route::group(['prefix' => 'rate'], function () {
        //done     
        Route::post('/teacher/{teacher}', [RateController::class, 'rateTeacher']);
        //done     
        Route::post('/academy/{academy}', [RateController::class, 'rateAcademy']);
    });
    // Route::group(['prefix' => 'notification'], function () {
    //     //     
    //     Route::get('/course/{course}', [NotificationController::class, 'showCourseNotifications']);
    //     //     
    //     Route::get('acceptAcademy', [NotificationController::class, 'showAcademyNotifications']);
    //     //     
    //     Route::get('offers', [NotificationController::class, 'showOffersNotifications']);
    //     //     
    //     Route::get('lesson/{lessonNotification}', [NotificationController::class, 'showLessonNotification']);
    //     //     
    //     Route::get('/academy/{academyNotification}', [NotificationController::class, 'showAcademyNotification']);
    //     //     
    //     Route::get('offer/{offerNotification}', [NotificationController::class, 'showOfferNotification']);
    // });

    Route::group(['prefix' => 'offers'], function () {
        //done     
        Route::get('/', [OfferStudentController::class, 'index']);
        //done     
        Route::get('/requests', [OfferStudentController::class, 'showOfferRequests']);
        //done    
        Route::get('/enroll/{offer}', [OfferStudentController::class, 'enrollToOffer']);
        //done    
        Route::delete('delete-request/{offer}', [OfferStudentController::class, 'delete']);
        //     
        // Route::get('ten', [OfferStudentController::class, 'tenOffers']);
    });

    Route::group(['prefix' => 'academy'], function () {
        //done     
        Route::get('/all', [AcademyStudentController::class, 'index']);
        //skip
        Route::get('{academy}/allcourses', [AcademyStudentController::class, 'allAcademyCourses']);
        //skip
        Route::get('/{academy}', [AcademyStudentController::class, 'show']);
        //skip
        Route::post('rate/{academy}', [AcademyStudentController::class, 'rate']);
    });
});

Route::group(['prefix' => 'teacher', 'middleware' => ['auth:sanctum', 'teacher']], function () {

    Route::group(['prefix' => 'profile'], function () {
        //done  
        Route::post('updatePhoto', [ProfileTeacherController::class, 'updatePhoto']);
        //done  
        Route::get('/', [ProfileTeacherController::class, 'show']);
        //done
        Route::post('/', [ProfileTeacherController::class, 'update']);
        //done
        Route::post('/change-password', [ProfileTeacherController::class, 'changePassword']);
        //done   
        Route::post('upload-post', [ProfileTeacherController::class, 'uploadPost']);
        //done   
        Route::get('my-posts', [ProfileTeacherController::class, 'myPosts']);
    });

    Route::group(['prefix' => 'courses'], function () {
        
        Route::get('/get_course_student/{course}', [CourseTeacherController::class, 'get_course_student']);
        //request(newApi)
        Route::post('/addExam/{course}', [CourseTeacherController::class, 'addExam']);
        //(newApi)c
        Route::delete('/deleteExam/{course}', [CourseTeacherController::class, 'deleteExam']);
        //done   
        Route::get('/', [CourseTeacherController::class, 'index']);
        //done   
        Route::get('/{course}', [CourseTeacherController::class, 'show']);
        //done     
        Route::post('/{course}/add-lesson', [InstituesTeacherController::class, 'addLesson']);
        //done     
        Route::get('/{course}/show-lessons', [InstituesTeacherController::class, 'showLessons']);
    });

    Route::group(['prefix' => 'institutes'], function () {
        //done
        Route::get('/teacherAcademy', [InstituesTeacherController::class, 'academies']);
        //don't
        Route::post('{id}/add-request', [InstituesTeacherController::class, 'store']);
        //done     
        Route::get('/pending-requests', [InstituesTeacherController::class, 'pendingRequests']);
        //done    
        Route::delete('{order}/cancel-request', [InstituesTeacherController::class, 'cancelRequest']);
        //done     
        Route::get('students/{course}', [InstituesTeacherController::class, 'showStudents']);
        //done     
        Route::get('courses-history', [InstituesTeacherController::class, 'coursesHistory']);
    });
});


Route::group(['prefix' => 'academy-admin', 'middleware' => ['auth:sanctum', 'academyAdmin']], function () {

    Route::group(['prefix' => 'profile'], function () {
        //done
        Route::get('6+3r54rt', [AcademyAdminProfilecontroller::class, 'show']);
        //done     
        Route::post('/update', [AcademyAdminProfilecontroller::class, 'update']);
        //done     
        Route::post('change-password', [AcademyAdminProfilecontroller::class, 'changePassword']);
    });
    Route::group(['prefix' => 'courses'], function () {
        Route::get('/get_course_student/{course}', [AcademyAdminCourseController::class, 'get_course_student']);
        //Ali    
        Route::post('/{course}/add-schedule', [AcademyAdminCourseController::class, 'addCourseSchedule']);
        //photo 
        Route::post('/{course}/update', [AcademyAdminCourseController::class, 'update']);
        //photo
        Route::post('/', [AcademyAdminCourseController::class, 'store']);
        //i want to delete it
        Route::get('/offers_unapproved', [AcademyAdminCourseController::class, 'unapprovedOffers']);
        //done
        Route::post('/offers_accept/{offerId}/{studentId}', [AcademyAdminCourseController::class, 'acceptOffer']);
        //done
        Route::post('/offers_reject/{offerId}/{studentId}', [AcademyAdminCourseController::class, 'rejectOffer']);
    });
    Route::group(['prefix' => 'teachers'], function () {
        //done     
        Route::get('/', [AcademyAdminTeacherController::class, 'index']);
        //done     
        Route::get('/requests', [AcademyAdminTeacherController::class, 'showTeacherRequests']);
        //done     
        Route::get('/accept-teacher/{teacher}', [AcademyAdminTeacherController::class, 'acceptTeacher']);
        //done   
        Route::delete('/reject-teacher/{teacher}', [AcademyAdminTeacherController::class, 'rejectTeacher']);
    });
    Route::group(['prefix' => 'students'], function () {
        //done    
        Route::get('/', [AcademyAdminStudentController::class, 'index']);
        //done     
        Route::get('/requests', [AcademyAdminStudentController::class, 'showStudentRequests']);
        //done     
        Route::get('/accept-student/{student}', [AcademyAdminStudentController::class, 'acceptStudent']);
        //done     
        Route::delete('/reject-student/{student}', [AcademyAdminStudentController::class, 'rejectStudent']);
    });
    Route::group(['prefix' => 'courses'], function () {

        // //     
        // Route::get('/inactive', [AcademyAdminCourseController::class, 'inactiveCourses']);
        // //     
        // Route::get('/active', [AcademyAdminCourseController::class, 'activeCourses']);
        //done  
        Route::get('/addStudentToCourse/{course}/{student}', [AcademyAdminStudentController::class, 'addStudentToCourse']);
    });
    // Route::group(['prefix' => 'exams'], function () {
    //     //     
    //     // Route::post('/addExam/{course}', [AcademyAdminExamController::class, 'addExam']);
    //     //     
    //     Route::delete('deleteExam/{course}', [AcademyAdminExamController::class, 'deleteExam']);
    // });
});


Route::group(['prefix' => 'super-admin', 'middleware' => ['auth:sanctum', 'superAdmin']], function () {
    //
    Route::get('showRequests', [RequestMangeController::class, 'showRequests']);
    //   
    Route::get('accept-academy/{academyPending}', [RequestMangeController::class, 'acceptAcademy']);
    //     
    Route::get('reject-academy/{academyPending}', [RequestMangeController::class, 'rejectrequest']);
});

//route that access to all
//done    
Route::get('academies', [GeneralController::class, 'academies']);
//done  (edit the respone i added teacher )   
Route::get('courses', [GeneralController::class, 'courses']);
//done     
Route::get('courses/{academy}', [GeneralController::class, 'courseInAcademy']);
//done     
Route::get('offers', [GeneralController::class, 'offers']);
//done     
Route::get('offer/{offer}', [GeneralController::class, 'offer']);
//done    
Route::get('academy/{academy}', [GeneralController::class, 'academy']);
//done     
Route::get('teacher/{teacher}', [GeneralController::class, 'teacher']);
//done     
Route::post('search-academies', [AcademyStudentController::class, 'academySearch']);


Route::post('/updateAcademyImage/{academy}', [GeneralController::class, 'updateImage']);
Route::post('/updateAcademyManagerImage/{admin}', [GeneralController::class, 'updateMPhoto']);
Route::post('/updateTeacherImage/{teacher}', [GeneralController::class, 'updateTPhoto']);

Route::post('/updatePostImage/{post}', [GeneralController::class, 'updatePostImage']);
Route::post('/updateStudentPhoto/{student}', [GeneralController::class, 'updateStudentPhoto']);