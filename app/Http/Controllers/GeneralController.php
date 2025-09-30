<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Student\RateController;
use App\Models\Academy;
use App\Models\AcademyAdminstrator;
use App\Models\Course;
use App\Models\Offer;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherPost;
use Illuminate\Http\Request;
use Illuminate\Notifications\Action;

class GeneralController extends Controller
{
    public function academies()
    {
        $academies = Academy::all();
        return [
            'status' => true,
            'message' => 'done seccussfuly',
            'data' => $academies
        ];
    }


    public function courseInAcademy(Academy $academy)
    {
        $courses = $academy->courses()->get();
        return [
            'status' => true,
            'message' => 'dome seccussfully',
            'data' => $courses
        ];
    }
    public function courses()
    {
        // جلب الكورسات مع الأساتذة المرتبطين فيها
        $courses = Course::with('teacher')->get();

        return response()->json([
            'status' => 200,
            'courses' => $courses
        ]);
    }

    public function offers()
    {
        $offers = Offer::all();
        return [
            'status' => true,
            'message' => 'done successfuly',
            'data' => $offers
        ];
    }
    public function offer(Offer $offer)
    {
        return response()->json([
            'status' => true,
            'message' => 'done successfully',
            'data' => $offer
        ]);
    }
    public function academy(Academy $academy)
    {
        $rate = RateController::getAcademyRate($academy);
        $academy->load('students', 'teachers', 'offers');
        $academy['rate'] = $rate;
        return response()->json([
            'status' => true,
            'message' => 'done successfully',
            'data' => $academy
        ]);
    }
    public function teacher(Teacher $teacher)
    {
        $rate = RateController::getTeacherRate($teacher);
        $teacher['rate'] = $rate;
        return response()->json([
            'status' => true,
            'message' => 'don successfully',
            'data' => $teacher
        ]);
    }



    public function updateImage(Request $request, Academy $academy)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        if ($academy->image && file_exists(public_path($academy->image))) {
            unlink(public_path($academy->image));
        }

        $path = $request->file('image')->store('academies', 'public');

        // تحديث العمود
        $academy->update([
            'image' => '/storage/' . $path
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Academy image updated successfully',
            'academy' => $academy
        ]);
    }


    public function updateTPhoto(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        if ($teacher->photo && file_exists(public_path($teacher->photo))) {
            unlink(public_path($teacher->photo));
        }

        $path = $request->file('photo')->store('teachers', 'public');

        $teacher->update([
            'photo' => '/storage/' . $path
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Teacher photo updated successfully',
            'teacher' => $teacher
        ]);
    }


    public function updateMPhoto(Request $request, AcademyAdminstrator $admin)
    {
        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        if ($admin->photo && file_exists(public_path($admin->photo))) {
            unlink(public_path($admin->photo));
        }

        $path = $request->file('photo')->store('academy_admins', 'public');

        $admin->update([
            'photo' => '/storage/' . $path
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Academy administrator photo updated successfully',
            'admin'   => $admin
        ]);
    }


   public function updatePostImage(Request $request, TeacherPost $post)
{
    $validated = $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
    ]);

    if ($post->image && file_exists(public_path($post->image))) {
        unlink(public_path($post->image));
    }

    $path = $request->file('image')->store('teacher_posts', 'public');

    $post->update([
        'image' => '/storage/' . $path
    ]);

    return response()->json([
        'status'  => true,
        'message' => 'Post image updated successfully',
        'post'    => $post
    ]);
}


public function updateStudentPhoto(Request $request, Student $student)
{
    $validated = $request->validate([
        'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
    ]);

    if ($student->photo && file_exists(public_path($student->photo))) {
        unlink(public_path($student->photo));
    }

    $path = $request->file('photo')->store('students', 'public');

    $student->update([
        'photo' => '/storage/' . $path
    ]);

    return response()->json([
        'status'  => true,
        'message' => 'Student photo updated successfully',
        'student' => $student
    ]);
}
}