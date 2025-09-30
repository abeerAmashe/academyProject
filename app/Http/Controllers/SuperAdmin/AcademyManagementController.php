<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\AcademyAdminstrator;
use App\Models\AcademyNotification;
use App\Models\AcademyPending;
use App\Models\AcademyStudent;
use App\Models\AcademyTeacher;
use Illuminate\Http\Request;

class AcademyManagementController extends Controller
{
    /**
     * Get all academies
     */
    public function academies()
    {
        try {
            $academies = Academy::with(['admin', 'courses', 'teachers', 'students'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $academies
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch academies: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all academy administrators
     */
    public function academyAdministrators()
    {
        try {
            $administrators = AcademyAdminstrator::with(['academy'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $administrators
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch academy administrators: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all academy notifications
     */
    public function academyNotifications()
    {
        try {
            $notifications = AcademyNotification::with(['academy'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $notifications
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch academy notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all pending academy requests
     */
    public function academyPending()
    {
        try {
            $pending = AcademyPending::with(['academyAdmin'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $pending
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch pending academies: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all academy students
     */
    public function academyStudents()
    {
        try {
            $students = AcademyStudent::with(['academy', 'student'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $students
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch academy students: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all academy teachers
     */
    public function academyTeachers()
    {
        try {
            $teachers = AcademyTeacher::with(['academy', 'teacher'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $teachers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch academy teachers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific academy with all related data
     */
    public function showAcademy($id)
    {
        try {
            $academy = Academy::with([
                'admin',
                'courses',
                'teachers.teacher',
                'students.student',
                'notifications'
            ])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $academy
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Academy not found: ' . $e->getMessage()
            ], 404);
        }
    }
}