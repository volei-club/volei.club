<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Training;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\UserService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected $attendanceService;
    protected $userService;

    public function __construct(AttendanceService $attendanceService, UserService $userService)
    {
        $this->attendanceService = $attendanceService;
        $this->userService = $userService;
    }

    /**
     * List attendance for a specific training + date.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'training_id' => 'required|uuid|exists:trainings,id',
            'date' => 'required|date',
        ]);

        $training = $this->attendanceService->getTrainingById($request->training_id, ['squad.users']);

        if ($user->role === 'antrenor' && $training->coach_id !== $user->id) {
            return response()->json(['message' => 'Acces interzis.'], 403);
        }

        $data = $this->attendanceService->getAttendance($training, $request->date, $user);

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    /**
     * Mark attendance for a single athlete.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['administrator', 'manager', 'antrenor'])) {
            return response()->json(['message' => 'Acces interzis.'], 403);
        }

        $validated = $request->validate([
            'training_id' => 'required|uuid|exists:trainings,id',
            'user_id' => 'required|uuid|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|in:prezent,absent,motivat',
            'notes' => 'nullable|string|max:500',
        ]);

        $training = $this->attendanceService->getTrainingById($validated['training_id']);

        if ($user->role === 'antrenor' && $training->coach_id !== $user->id) {
            return response()->json(['message' => 'Nu esti antrenorul acestui antrenament.'], 403);
        }

        $attendance = $this->attendanceService->markAttendance($validated, $user);

        return response()->json(['status' => 'success', 'data' => $attendance->load('user')], 201);
    }

    /**
     * Delete an attendance record.
     */
    public function destroy(string $id)
    {
        $user = request()->user();
        $attendance = $this->attendanceService->getAttendanceById($id);

        if (!in_array($user->role, ['administrator', 'manager', 'antrenor'])) {
            return response()->json(['message' => 'Acces interzis.'], 403);
        }

        $attendance->delete();
        return response()->json(null, 204);
    }

    /**
     * Return calendar sessions for the current user.
     */
    public function myCalendar(Request $request)
    {
        $user = $request->user();
        $weeks = (int)$request->input('weeks', 4);
        $childId = $request->input('child_id');

        if ($user->role === 'parinte' && $childId) {
            $isParent = $user->children()->where('users.id', $childId)->exists();
            if (!$isParent) {
                return response()->json(['message' => 'Acces interzis.'], 403);
            }
            $subject = $this->userService->getUserById($childId);
        }
        else {
            $subject = $user;
        }

        $sessions = $this->attendanceService->generateCalendar($subject, $weeks);

        return response()->json(['status' => 'success', 'data' => $sessions]);
    }
}
