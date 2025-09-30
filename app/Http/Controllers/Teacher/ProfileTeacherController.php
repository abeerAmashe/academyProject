<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Student\RateController;
use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileTeacherController extends Controller
{
	protected function uploadImage($request)
	{
		$courseImage = $request->file('image');
		$imageName = time() . $courseImage->getClientOriginalName();
		$courseImage->move(public_path('course-images'), $courseImage);
		$imageUrl = "teacher/images/$imageName";
		return $imageUrl;
	}

	//change Teacher Password
	public function changePassword(Request $request)
	{
		$validatedData = $request->validate([
			'current_password' => 'required|string',
			'new_password'     => 'required|string|min:8',
		]);

		$user = auth()->user(); // المستخدم الحالي

		if (!Hash::check($validatedData['current_password'], $user->password)) {
			return response()->json([
				'status' => 422,
				'errors' => [
					'current_password' => ['The current password is incorrect.']
				]
			]);
		}

		// تحديث كلمة المرور
		$user->password = Hash::make($validatedData['new_password']);
		$user->save();

		return response()->json([
			'status' => 200,
			'message' => 'Password changed successfully'
		]);
	}

	//Show a student's profile
	public function show()
	{
		$teacher = Teacher::where('user_id', auth()->id())->first();
		$teacher['email'] = User::where('id', auth()->id())->first()['email'];
		$teacher['rate'] = RateController::getTeacherRate($teacher);
		return response()->json([
			'teacher info' => $teacher
		], 200);
	}


	//Update a student's profile
	// public function update(Request $request)
	// {
	// 	$validatedData = $request->validate([
	// 		'first_name'   => 'nullable|string|max:255',
	// 		'last_name'    => 'nullable|string|max:255',
	// 		'phone_number' => 'nullable|string|max:20',
	// 		'photo'        => 'nullable|image|max:2048',
	// 	]);

	// 	$teacher = Teacher::where('user_id', auth()->id())->first();

	// 	if (!$teacher) {
	// 		return response()->json([
	// 			'status' => 404,
	// 			'message' => 'Teacher not found.',
	// 		]);
	// 	}

	// 	$teacher->update($validatedData);

	// 	return response()->json([
	// 		'status' => 200,
	// 		'message' => 'Profile updated successfully',
	// 		'teacher' => $teacher,
	// 	]);
	// }

	public function update(Request $request)
	{
		$validatedData = $request->validate([
			'first_name'   => 'nullable|string|max:255',
			'last_name'    => 'nullable|string|max:255',
			'phone_number' => 'nullable|string|max:20',
			'photo'        => 'nullable|image|max:2048',
		]);

		$teacher = Teacher::where('user_id', auth()->id())->first();

		if (!$teacher) {
			return response()->json([
				'status'  => 404,
				'message' => 'Teacher not found.',
			], 404);
		}

		if ($request->hasFile('photo')) {
			if ($teacher->photo && str_contains($teacher->photo, 'storage/teachers/')) {
				$oldPath = str_replace(asset('storage') . '/', '', $teacher->photo);
				Storage::disk('public')->delete($oldPath);
			}

			$path = $request->file('photo')->store('teachers', 'public');
			$validatedData['photo'] = '/storage/' . $path;
		}

		$teacher->update($validatedData);

		return response()->json([
			'status'  => 200,
			'message' => 'Profile updated successfully',
			'teacher' => $teacher,
		], 200);
	}



	// public function uploadPost(Request $request)
	// {
	// 	$imageUrl = '';
	// 	if ($request->hasFile('image')) {
	// 		$imageUrl = $this->uploadImage($request);
	// 	}
	// 	$teacher = Teacher::where('user_id', auth()->id())->first();
	// 	$post = $teacher->posts()->create([
	// 		'title' => $request->title,
	// 		'image' => $imageUrl
	// 	]);
	// 	return response()->json([
	// 		'status' => 200,
	// 		'message' => 'add post successfully',
	// 		'data' => $post
	// 	]);
	// }

	public function uploadPost(Request $request)
	{
		// تحقق من البيانات
		$request->validate([
			'title' => 'required|string|max:255',
			'image' => 'required|image|max:2048', // الصورة اختيارية
		]);

		$teacher = Teacher::where('user_id', auth()->id())->first();

		if (!$teacher) {
			return response()->json([
				'status'  => 404,
				'message' => 'Teacher not found',
			], 404);
		}

		$imageUrl = null;

		// إذا في صورة مرفوعة
		if ($request->hasFile('image')) {
			// تخزين الصورة في storage/app/public/posts
			$path = $request->file('image')->store('posts', 'public');
			$imageUrl = '/storage/' . $path;
		}

		// إنشاء البوست
		$post = $teacher->posts()->create([
			'title' => $request->title,
			'image' => $imageUrl,
		]);

		return response()->json([
			'status'  => 200,
			'message' => 'Post added successfully',
			'data'    => $post,
		]);
	}


	public function myPosts()
	{
		$teacher = Teacher::where('user_id', auth()->id())->first();
		$posts = $teacher->posts()->get();
		return response()->json([
			'status' => 200,
			'message' => 'success',
			'data' => $posts
		]);
	}




	public function updatePhoto(Request $request)
	{
		$request->validate([
			'photo' => 'image|mimes:jpeg,png,jpg|max:2048'
		]);

		$teacher = Teacher::where('user_id', auth()->id())->first();

		// رفع الصورة
		if ($request->hasFile('photo')) {
			$path = $request->file('photo')->store('teachers', 'public');
			$validatedData['photo'] = '/storage/' . $path;
		}
		// تحديث مسار الصورة
		// $teacher->photo = asset('storage/' . $path);
		$teacher->update($validatedData);
		return response()->json([
			'message' => 'Photo updated successfully',
			'photo_url' => $teacher->photo
		]);
	}
}
