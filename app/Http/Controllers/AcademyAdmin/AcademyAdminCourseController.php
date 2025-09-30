<?php

namespace App\Http\Controllers\AcademyAdmin;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\AcademyAdminstrator;
use App\Models\Course;
use App\Models\Offer;
use App\Models\OfferStudent;
use Illuminate\Http\Request;

class AcademyAdminCourseController extends Controller
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



	//AcademyAdmin can create courses
	public function store(Request $request)
	{
		$validated = $request->validate([
			'name' => 'required|string|max:255',
			'price' => 'nullable|integer|min:0',
			'hours' => 'nullable|integer|min:0',
			'course_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // تحقق من نوع الملف
			'seats' => 'nullable|integer|min:0',
			'description' => 'required|string',
			'hasExam' => 'nullable|boolean',
			'start_time' => 'required|date',
			'end_time' => 'required|date|after_or_equal:start_time',
			'academy_id' => 'required|exists:academies,id',
			'teacher_id' => 'required|exists:teachers,id', // ✅ إضافة teacher_id

		]);

		$validated['active'] = true;

		if ($request->hasFile('course_image')) {
			$path = $request->file('course_image')->store('courses', 'public');
			$validated['course_image'] = '/storage/' . $path;
		}

		$course = Course::create($validated);
		$course->teacher()->attach($validated['teacher_id']);


		return response()->json([
			'message' => 'Course created successfully',
			'course' => $course
		], 201);
	}




	// update Course Information
	public function update(Request $request, Course $course)
	{
		$validatedData = $request->validate([
			'name' => 'string|max:255',
			'description' => 'string',
			'price' => 'integer|min:0',
			'hours' => 'integer|min:0',
			'seats' => 'integer|min:0',
			'course_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp',
		]);

		if ($request->hasFile('course_image')) {
			$path = $request->file('course_image')->store('courses', 'public');
			$validatedData['course_image'] = '/storage/' . $path;
		}

		$course->update($validatedData);

		return response()->json([
			'success' => 'Course updated successfully',
			'course' => $course
		]);
	}


	public function addCourseSchedule(Request $request, Course $course)
	{
		$validatedData = $request->validate([
			'day' => 'required|string',
			'start_hour' => 'required|date_format:H:i',
			'end_hour' => 'required|date_format:H:i',
			'start_date' => 'required|date',
			'end_date' => 'required|date',
		]);

		$schedule = $course->annualSchedules()->create($validatedData);

		return response()->json([
			'message' => 'success',
			'status' => '200',
			'data' => $schedule,
		]);
	}



	///////////////
	public function inactiveCourses()
	{
		$admin = AcademyAdminstrator::where('user_id', auth()->id())->first();
		$academy = $admin->academy()->first();
		$courses = $academy->courses()->where('active', false)->get();
		return response()->json([
			'status' => 200,
			'message' => 'done successfully',
			'data' => $courses
		]);
	}


	public function activeCourses()
	{
		$admin = AcademyAdminstrator::where('user_id', auth()->id())->first();
		$academy = $admin->academy()->first();
		// return $academy ;
		$courses = $academy->courses()->where('active', true)->get();
		return response()->json([
			'status' => 200,
			'message' => 'done successfully',
			'data' => $courses
		]);
	}



	public function unapprovedOffers()
	{
		$offers = Offer::whereHas('students', function ($query) {
			$query->where('offer_student.approved', 0);
		})
			->with(['students' => function ($query) {
				$query->where('offer_student.approved', 0)
					->select('students.id', 'first_name', 'last_name', 'phone_number', 'user_id');
			}])
			->get();

		return response()->json($offers);
	}

	public function acceptOffer($offerId, $studentId)
	{
		$offerStudent = OfferStudent::where('offer_id', $offerId)
			->where('student_id', $studentId)
			->firstOrFail();

		$offerStudent->update([
			'approved' => 1
		]);

		return response()->json([
			'message' => 'Offer accepted successfully',
			'data' => $offerStudent
		]);
	}

	public function rejectOffer($offerId, $studentId)
	{
		$offerStudent = OfferStudent::where('offer_id', $offerId)
			->where('student_id', $studentId)
			->firstOrFail();

		$offerStudent->update([
			'approved' => -1
		]);

		return response()->json([
			'message' => 'Offer rejected successfully',
			'data' => $offerStudent
		]);
	}
}