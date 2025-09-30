<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\Offer;
use App\Models\OfferStudent;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OfferStudentController extends Controller
{
    public function index(){
        $student = Student::where('user_id' , auth()->id())->first() ;
        $offers = $student->offers()
        ->wherePivot('approved' , true)
        ->get() ;
        return response()->json([
            'status' => 200 ,
            'message' => 'done successfully',
            'data' => $offers 
        ]);
    }
    public function showOfferRequests(){
        $student =  Student::where('user_id' , auth()->id())->first();
        $student_id = $student['id'];
		$requests = OfferStudent::where('student_id' , $student_id)->get();
		$i = 0 ;
		foreach($requests as $request){
            $offer = Offer::where('id' , $request->offer_id)->first() ;
            $academyTime = $offer->academy()->first()['delete_time'] ;
			if (now()->greaterThan( $request['created_at']->addDays($academyTime))){
				$request->delete() ;
				unset($requests[$i]); 
			}
			$i++ ;
		}
        $offers = $student->offers()->wherePivot('approved' , false)->get();
		return response()->json([
			'status' => 200,
			'message'=>'done successfully',
			'data'=>$offers
		]);
    }
    public function delete(Offer $offer){
        $student = Student::where('user_id' ,auth()->id())->first() ;
        $student->offers()->detach($offer);
        return response()->json([
            'status' => 200 ,
            'message' => 'delete it successfully'
        ]);
    }
    // public function enrollToOffer(Offer $offer){
    //     $student = Student::where('user_id' , auth()->id())->first();
    //     OfferStudent::create([
    //         'student_id' => $student->id ,
    //         'offer_id' => $offer->id ,
    //         'approved' => 0 
    //     ]);
    //     return response()->json([
    //         'status' => 200 ,
    //         'message' => 'done successfully'
    //     ]);
    // }


public function enrollToOffer(Offer $offer)
{
    $student = Student::where('user_id', auth()->id())->first();

    if (!$student) {
        return response()->json([
            'status' => 404,
            'message' => 'Student not found'
        ]);
    }

    // تحقق إذا موجود أصلاً
    $exists = OfferStudent::where('student_id', $student->id)
                          ->where('offer_id', $offer->id)
                          ->exists();

    if ($exists) {
        return response()->json([
            'status' => 409, // Conflict
            'message' => 'You are already enrolled in this offer'
        ]);
    }

    OfferStudent::create([
        'student_id' => $student->id,
        'offer_id'   => $offer->id,
        'approved'   => 0
    ]);

    return response()->json([
        'status'  => 200,
        'message' => 'Done successfully'
    ]);
}

    public function tenOffers(){
        $student = Student::where('user_id' , auth() -> id())->first() ;
        $academies =  $student->academies()->wherePivot('approved' ,true)->get();
        $offers = $academies[0]->offers()->get() ;
        $count =  count($academies) ;
        $i = 1;
        while($i <$count ){
            $offers = $offers->merge( $academies[$i]->offers()->get() ) ;
            if (count($offers) >=10)
            break ;
            $i++ ;
        }
        if ( count($offers) > 10){
            $data[0] = $offers[0] ;
            for($l = 1; $l<10 ;$l++){
                $data[$i] = $offers[$i];
            }
       return response()->json([
            'status' => 200 ,
            'message' => 'done ' ,
            'data' => $data
        ]) ;
       }else {
        return response()->json([
            'status' => 200 ,
            'message' => 'done 1' ,
            'data' => $offers
        ]) ;
       }

    }


    
}