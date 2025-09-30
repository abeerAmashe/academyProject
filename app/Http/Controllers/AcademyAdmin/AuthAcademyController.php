<?php

namespace App\Http\Controllers\AcademyAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademyPhoto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthAcademyController extends Controller
{
    public function register(Request $request)
{
    $credentials = $request->validate([
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'phone_number' => 'required|string',
        'name' => 'required|string',
        'location' => 'required|string',
        'license_number' => 'required',
        'description' =>  'required|string',
        'email' => 'required|string|email|unique:users,email',
        'password' => 'required|string',
        'photo' => 'required|file|image|max:2048',
        'photos.*' => 'nullable|file|image|max:2048'
    ]);

    $user = User::query()->create([
        'role_id' => 2,
        'email' => $credentials['email'],
        'password' => Hash::make($credentials['password']),
        'email_verified_at' => now()
    ]);

    $admin = $user->academyAdmin()->create([
        'first_name' => $credentials['first_name'],
        'last_name' => $credentials['last_name'],
        'phone_number' => $credentials['phone_number']
    ]);

    $photoPath = $request->file('photo')->store('academy_photos', 'public');
    $imageUrl = '/storage/' . $photoPath;

    $en = $request->boolean('english');
    $fr = $request->boolean('french');
    $sp = $request->boolean('spanish');
    $ge = $request->boolean('germany');

    $pendingAcademy = $admin->AcademyPending()->create([
        'name' => $request->name,
        'location' => $request->location,
        'license_number' => $request->license_number,
        'description' =>  $request->description,
        'photo' => $imageUrl,
        'english' => $en,
        'french' => $fr,
        'spanish' => $sp,
        'germany' => $ge,
    ]);

  

    $token = $user->createToken('Personal Access Token')->plainTextToken;

    return response()->json([
        'status' => true,
        'message' => 'Registration done successfully',
        'data' => $admin,
        'token' => $token,
        'token_type' => 'Bearer'
    ]);
}

    //
}