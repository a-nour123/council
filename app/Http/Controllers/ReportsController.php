<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Faculty;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function departmentForAgendas(Request $request)
    {
        $facultyId = $request->facultyId;
        $yearId = $request->yearId;

        $filteredDepartments = Department::query()
            ->where('faculty_id', $facultyId)
            ->withCount([
                'agendas as total_agendas' => function ($query) use ($yearId) {
                    $query->where('yearly_calendar_id', $yearId);
                },
                'agendas as total_pending_agendas' => function ($query) use ($yearId) {
                    $query->where('yearly_calendar_id', $yearId)->WithPending();
                },
                'agendas as total_accepted_agendas' => function ($query) use ($yearId) {
                    $query->where('yearly_calendar_id', $yearId)->WithAccepted();
                },
                'agendas as total_rejected_agendas' => function ($query) use ($yearId) {
                    $query->where('yearly_calendar_id', $yearId)->WithRejected();
                },
            ])
            ->get(['id', 'ar_name', 'faculty_id'])
            ->map(function ($department) {
                return [
                    'id' => $department->id,
                    'name' => $department->ar_name,
                    'total_agendas' => $department->total_agendas,
                    'total_pending_agendas' => $department->total_pending_agendas,
                    'total_accepted_agendas' => $department->total_accepted_agendas,
                    'total_rejected_agendas' => $department->total_rejected_agendas,
                ];
            });

        return response()->json($filteredDepartments);
    }

    public function departmentForSession(Request $request)
    {
        $facultyId = $request->facultyId;
        $yearId = $request->yearId;

        $filteredDepartments = Department::query()
            ->where('faculty_id', $facultyId)
            ->withCount([
                'sessions as total_sessions' => function ($query) use ($yearId) {
                    $query->where('yearly_calendar_id', $yearId);
                },
                'sessions as total_pending_sessions' => function ($query) use ($yearId) {
                    $query->where('yearly_calendar_id', $yearId)->TotalPendingSession();
                },
                'sessions as total_accepted_sessions' => function ($query) use ($yearId) {
                    $query->where('yearly_calendar_id', $yearId)->TotalAcceptedSession();
                },
                'sessions as total_rejected_sessions' => function ($query) use ($yearId) {
                    $query->where('yearly_calendar_id', $yearId)->TotalRejectedSession();
                },
                'sessions as total_approvedDecision_sessions' => function ($query) use ($yearId): void {
                    $query->where('yearly_calendar_id', $yearId)->whereHas('sessionDecisions', function ($query) {
                        $query->TotalApprovedSessionDecision(); // Apply the Approved session scope
                    })->distinct('session_id'); // Ensure uniqueness based on session_id
                },
                'sessions as total_rejectedDecision_sessions' => function ($query) use ($yearId): void {
                    $query->where('yearly_calendar_id', $yearId)->whereHas('sessionDecisions', function ($query) {
                        $query->TotalRejectedSessionDecision(); // Apply the Approved session scope
                    })->distinct('session_id'); // Ensure uniqueness based on session_id
                },
            ])
            ->get(['id', 'ar_name', 'faculty_id'])
            ->map(function ($department) {
                $total_pendingDecision_sessions = $department->total_sessions - ($department->total_approvedDecision_sessions + $department->total_rejectedDecision_sessions);

                return [
                    'id' => $department->id,
                    'name' => $department->ar_name,
                    'total_sessions' => $department->total_sessions,
                    'total_pending_sessions' => $department->total_pending_sessions,
                    'total_accepted_sessions' => $department->total_accepted_sessions,
                    'total_rejected_sessions' => $department->total_rejected_sessions,
                    'total_approvedDecision_sessions' => $department->total_approvedDecision_sessions,
                    'total_rejectedDecision_sessions' => $department->total_rejectedDecision_sessions,
                    'total_pendingDecision_sessions' => $total_pendingDecision_sessions,
                ];
            });

        return response()->json($filteredDepartments);
    }

    public function facultyForSession(Request $request)
    {
        $yearId = $request->yearId;

        $filteredFaculties = Faculty::query()
            ->withCount([
                'sessions as total_sessions' => function ($query) use ($yearId) {
                    $query->where('yearly_calendar_id', $yearId);
                },
                'sessions as total_pending_sessions' => function ($query) use ($yearId) {
                    $query->where('yearly_calendar_id', $yearId)->TotalPendingSession();
                },
                'sessions as total_accepted_sessions' => function ($query) use ($yearId) {
                    $query->where('yearly_calendar_id', $yearId)->TotalAcceptedSession();
                },
                'sessions as total_rejected_sessions' => function ($query) use ($yearId) {
                    $query->where('yearly_calendar_id', $yearId)->TotalRejectedSession();
                },
                'sessions as total_approvedDecision_sessions' => function ($query) use ($yearId): void {
                    $query->where('yearly_calendar_id', $yearId)->whereHas('facultySessionDecisions', function ($query) {
                        $query->TotalApprovedSessionDecision(); // Apply the Approved session scope
                    })->distinct('session_id'); // Ensure uniqueness based on session_id
                },
                'sessions as total_rejectedDecision_sessions' => function ($query) use ($yearId): void {
                    $query->where('yearly_calendar_id', $yearId)->whereHas('facultySessionDecisions', function ($query) {
                        $query->TotalRejectedSessionDecision(); // Apply the Approved session scope
                    })->distinct('session_id'); // Ensure uniqueness based on session_id
                },
            ])
            ->get(['id', 'ar_name'])
            ->map(function ($faculty) {
                $total_pendingDecision_sessions = $faculty->total_sessions - ($faculty->total_approvedDecision_sessions + $faculty->total_rejectedDecision_sessions);

                return [
                    'id' => $faculty->id,
                    'name' => $faculty->ar_name,
                    'total_sessions' => $faculty->total_sessions,
                    'total_pending_sessions' => $faculty->total_pending_sessions,
                    'total_accepted_sessions' => $faculty->total_accepted_sessions,
                    'total_rejected_sessions' => $faculty->total_rejected_sessions,
                    'total_approvedDecision_sessions' => $faculty->total_approvedDecision_sessions,
                    'total_rejectedDecision_sessions' => $faculty->total_rejectedDecision_sessions,
                    'total_pendingDecision_sessions' => $total_pendingDecision_sessions,
                ];
            });
        // dd($filteredFaculties);
        return response()->json($filteredFaculties);
    }
}
