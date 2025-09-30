<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Teacher;

class CourseTeacherController extends Controller
{

	public function get_course_student(Course $course)
	{
		// جلب الطلاب المرتبطين فقط مع تحديد جدول students لتجنب التعارض
		$students = $course->students()
			->get([
				'students.id',
				'students.first_name',
				'students.last_name',
				'students.phone_number',
				'students.photo',
				'students.user_id'
			]);

		return response()->json([
			'status' => 200,
			'course_id' => $course->id,
			'course_name' => $course->name,
			'students' => $students
		]);
	}


	protected function uploadCourseImage($request)
	{
		$courseImage = $request->file('course_image');
		$imageName = time() . $courseImage->getClientOriginalName();
		$courseImage->move(public_path('course-images'), $courseImage);
		$imageUrl = "public/tourism/course-images/$imageName";
		return $imageUrl;
	}

	// return all the courses
	// public function index() {
	// 	$teacher = Teacher::where('user_id', auth()->id())->first();
	// 	$courses = $teacher->courses()->get();
	// 	return response()->json([
	// 		'status' => 200,
	// 		'message' => 'done succeefully',
	// 		'courses' => $courses
	// 	]);
	// }
	public function index()
	{
		$teacher = Teacher::where('user_id', auth()->id())->first();

		if (!$teacher) {
			return response()->json([
				'status' => 404,
				'message' => 'Teacher not found',
			], 404);
		}

		$courses = $teacher->courses()->get();

		return response()->json([
			'status' => 200,
			'message' => 'done successfully',
			'courses' => $courses,
		]);
	}


	// show specific course
	public function show(Course $course)
	{
		return response()->json([
			'status' => 200,
			'message' => 'done succeefully',
			'course' => $course->first()
		]);
	}

	//Teacher can create courses
	public function createCourse(Request $request)
	{
		$validatedData = $request->validate([
			'title' => 'required|string|max:255',
			'description' => 'required|string',
			'price' => 'required|integer',
			'hours' => 'required|integer',
			'seats' => 'required|integer',
			'course_image' => 'required|image',
			// other fields to validate
		]);
		$imageUrl = '';
		if ($request->hasFile('course_image')) {
			$imageUrl = $this->uploadCourseImage($request);
		}
		$teacher = Teacher::where('user_id', auth()->id())->first();

		$course = Course::query()->create($validatedData +
			['course_image' => $imageUrl]);

		$course->teachers()->attach($teacher->id);
		return response()->json([
			'success' => 'Course created successfully',
			'course' => $course
		]);
	}
	// update Course Information
	public function updateCourse(Request $request, Course $course)
	{
		$validatedData = $request->validate([
			'title' => 'required|string|max:255',
			'description' => 'required|string',
			'price' => 'required|integer',
			'hours' => 'required|integer',
			'seats' => 'required|integer',
			'course_image' => 'required|image',
			// other fields to validate
		]);
		$course->update($validatedData);

		return response()->json([
			'success' => 'Course updated successfully',
			'course' => $course
		]);
	}
	// Display a list of students enrolled in a course
	public function courseStudents(Course $course)
	{
		$teacher = Teacher::where('user_id', auth()->id())->first();
		$students = $course->students()->get();
		return response()->json([
			'teacher' => $teacher,
			'course' => $course,
			'students' => $students
		]);
	}
	// Remove a student from a course
	public function removeStudent(Request $request, Course $course)
	{
		$validatedData = $request->validate([
			'student_id' => 'required|exists:students,id',
		]);
		$course->students()->detach($validatedData['student_id']);

		return response()->json([
			'course' => $course,
			'success' => 'Student removed successfully'
		]);
	}

	public function destroy(Course $course)
	{
		$course->delete();
		return response()->json([
			'status' => 200,
			'message' => 'done succeefully',
			'message' => 'course deleted succfully'
		]);
	}

	public function addExam(Request $request, Course $course)
	{
		$validatedData = $request->validate([
			'questions' => 'required|array|min:1',
			'questions.*.value' => 'required|string',
			'questions.*.choise1' => 'required|string',
			'questions.*.choise2' => 'required|string',
			'questions.*.choise3' => 'required|string',
			'questions.*.choise4' => 'required|string',
			'questions.*.correct_choise' => 'required|integer|in:1,2,3,4',
		]);

		$exam = $course->exams()->create([]);

		foreach ($validatedData['questions'] as $questionData) {
			$exam->questions()->create($questionData);
		}

		return response()->json([
			'status' => true,
			'message' => 'Exam and questions added successfully',
			'exam' => $exam->load('questions')
		]);
	}


	public function deleteExam(Course $course)
	{
		if (!$course->exams)
			return response()->json([
				'status' => 201,
				'message' => 'thers is no exam in this course'
			]);

		$course->exams()->delete();

		return response()->json([
			'status' => 200,
			'message' => 'deleted successfully'
		]);
	}
}
