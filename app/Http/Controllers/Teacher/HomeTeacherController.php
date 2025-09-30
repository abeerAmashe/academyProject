<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\TeacherPost;

class HomeTeacherController extends Controller
{
        /*protected function uploadImages(Request $request) {

        foreach ($request->file('images') as $image) {
            $imageName = time().$image->getClientOriginalName();
            $image->move(public_path('images'), $image);
            $imageUrl = "teacher/images/$imageName";
            return $imageUrl;
            //$path = $image->store('public/images');
        }
        return response()->json([
            'status' => 200,
            'message' => 'success - Images uploaded successfully',
            'data' => [],
        ]);
    }*/

    public function test() {
        $academies = Course::with('rate');
        return $academies;
    }
    public function academySearch(Request $request){
        $key = $request->validate([
            'search_key' => 'required'
        ]);
        $academiesByName = Academy::where('name' , 'like' , "%$request->search_key%")
        ->get();
        $academiesByLocation = Academy::where('location' , 'like' , "%$request->search_key%")
        ->get();
        $academiesByLang = Academy::where( $request->search_key ,true)
        ->get();
        $responce = response()->json([
            'status'=>200,
            'message'=>'done successfully',
            'academiesByName' => $academiesByName,
            'academiesByLocation'=>$academiesByLocation,
            'academiesByLang'=>$academiesByLang
        ]);
    }
}
