<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Academy;
use App\Models\AcademyStudent;
use App\Models\Rate;
use App\Models\Course;
use App\Models\FeedBack;
use App\Models\RateTeacher;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Database\Seeders\AcademySeeder;

use function PHPUnit\Framework\returnSelf;

class AcademyStudentController extends Controller
{
	
	public function index()
	{
		$student =  Student::where('user_id', auth()->id())->first();
		$academies = $student->academies()
			->wherePivot('approved', 1)
			->get();
		$i = 0;
		foreach ($academies as $academy) {
			$raties = AcademyStudent::where('academy_id', $academy->id)
				->where('approved', true)
				->get();
			$finalRate = 0;
			$finalCountRate = 0;
			foreach ($raties as $rate) {
				$finalRate +=  $rate['rate'];
				$finalCountRate++;
			}
			$academies[$i]['rate'] = $finalRate / $finalCountRate;
			$i++;
		}
		$respnose = response()->json([
			'status' => 200,
			'message' => 'academies displayed seccussfuly',
			'data' => $academies
		]);
		return $respnose;
	}

	public function joinToAcademy(Request $request, Academy $academy)
	{
		$request->validate([
			'language' => 'nullable'
		]);

		$student = Student::where('user_id', auth()->id())->first();
		if ($student->academies()->wherePivot('academy_id', $academy['id'])->wherePivot('approved', false)->exists()) {
			return response()->json([
				'status' => false,
				'message' => 'you are already add join request to this academy'
			]);
		}
		$student->academies()->attach($academy, [
			'created_at' => now(),
			'updated_at' => now(),
			'enroll_date' => now()
		]);

		$response = response()->json([
			'status' => 200,
			'message' => 'done successfully',
		]);
		return $response;
	}
	// public function showRequest(){
	// 	$student_id = Student::where('user_id' , auth()->id())->first()['id'];
	// 	$requests = AcademyStudent::where('student_id' , $student_id)->get();
	// 	$i = 0 ;
	// 	foreach($requests as $request){

	// 		$academyTime = Academy::where('id' , $request->academy_id)->first()['delet_time'] ;
	// 		if (now()->greaterThan( $request['created_at']->addDays($academyTime))){
	// 			$request->delete() ;
	// 			unset($requests[$i]);
	// 		}
	// 		$request->load('academy');
	// 		$i++ ;
	// 	}
	// 	return response()->json([
	// 		'status' => 200,
	// 		'message'=>'done successfully',
	// 		'data'=>$requests
	// 	]);
	// }
	public function showRequest()
	{
		$student_id = Student::where('user_id', auth()->id())->first()?->id;

		$requests = AcademyStudent::where('student_id', $student_id)->get();
		$filteredRequests = [];

		foreach ($requests as $request) {
			$academy = Academy::find($request->academy_id);
			$academyTime = $academy?->delet_time ?? 0;

			// if ($request->created_at && now()->greaterThan($request->created_at->addDays($academyTime))) {
			//     $request->delete();
			//     continue;
			// }

			$request->load('academy');
			$filteredRequests[] = $request;
		}

		return response()->json([
			'status' => 200,
			'message' => 'done successfully',
			'data' => $filteredRequests
		]);
	}


	public function delete(Academy $academy)
	{
		$student = Student::where('user_id', auth()->id())->first();

		if (!$student) {
			return response()->json([
				'status' => 404,
				'message' => 'Student not found.'
			]);
		}
		
		$student->academies()->detach($academy->id);

		return response()->json([
			'status' => 200,
			'message' => 'deleted successfully'
		]);
	}


	public function addFeedBack(Academy $academy, Request $request)
	{

		$student_id = Student::where('user_id', auth()->id())->first()['id'];
		$test = AcademyStudent::where('academy_id', $academy->id)
			->where('student_id', $student_id)
			->where('approved', 1)
			->first();
		if ($test == null) {
			return response()->json([
				'status' => false,
				'message' => 'you are not member in this academy '
			]);
		}
		$data = $request->validate([
			'value' => 'required'
		]);
		$feedBack = FeedBack::create([
			'value' => $data['value'],
			'academy_id' => $academy->id
		]);
		return response()->json([
			'status' => 200,
			'message' => 'done successfully',
			'data' => $feedBack
		]);
	}
	// public function academySearch(Request $request)
	// {
	// 	$key = $request->validate([
	// 		'search_key' => 'required'
	// 	]);
	// 	$academiesByName = Academy::where('name', 'like', "%$request->search_key%")
	// 		->get();
	// 	$academiesByLocation = Academy::where('location', 'like', "%$request->search_key%")
	// 		->get();

	// 	if (
	// 		$request->search_key == 'english' ||
	// 		$request->search_key == 'french' ||
	// 		$request->search_key == 'spanish' ||
	// 		$request->search_key == 'germany'
	// 	)
	// 		$academiesByLang = Academy::where($request->search_key, true)
	// 			->get();
	// 	else $academiesByLang = Academy::find(1)->get();

	// 	$data = $academiesByName->merge($academiesByLocation);
	// 	$finalData = $data->merge($academiesByLang);

	// 	$response = response()->json([
	// 		'status' => 200,
	// 		'message' => 'done successfully',
	// 		'data' => $finalData
	// 	]);
	// 	return $response;
	// }

	public function academySearch(Request $request)
{
    $request->validate([
        'search_key' => 'required|string'
    ]);

    $searchKey = $request->search_key;

    $academiesByName = Academy::where('name', 'like', "%{$searchKey}%")->get();

    $academiesByLocation = Academy::where('location', 'like', "%{$searchKey}%")->get();

    $languages = ['english', 'french', 'spanish', 'germany'];
    if (in_array(strtolower($searchKey), $languages)) {
        $academiesByLang = Academy::where(strtolower($searchKey), true)->get();
    } else {
        $academiesByLang = collect([]); 
    }

    $finalData = $academiesByName
        ->merge($academiesByLocation)
        ->merge($academiesByLang)
        ->unique('id')
        ->values();

    return response()->json([
        'status' => 200,
        'message' => 'done successfully',
        'data' => $finalData
    ]);
}

	public function academy(Academy $academy)
	{

		$raties = AcademyStudent::where('academy_id', $academy->id)
			->where('approved', true)
			->get();
		$finalRate = 0;
		$finalCountRate = 0;
		foreach ($raties as $rate) {
			$finalRate +=  $rate['rate'];
			$finalCountRate++;
		}
		if ($finalCountRate == 0)
			$academy['rate'] = 0;
		else
			$academy['rate'] = $finalRate / $finalCountRate;
		$student_id = Student::where('user_id', auth()->id())->first()['id'];
		$academy['my_rate'] = AcademyStudent::where('academy_id', $academy->id)
			->where('student_id', $student_id)->first()['rate'];
		return response()->json([
			'status' => 200,
			'message' => 'done',
			'data' => $academy
		]);
	}
}