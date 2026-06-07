<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\Controller;
use App\Models\EmployeeShift;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ShiftCalendarController extends Controller
{
    public function index(Request $request): Response
    {
        $weekStart = Carbon::parse(
            $request->query('week', now()->startOfWeek()->toDateString())
        )->startOfDay();

        $assignments = EmployeeShift::query()
            ->with(['employee:id,full_name', 'shift:id,name,start_time,end_time'])
            ->whereBetween('date', [
                $weekStart->toDateString(),
                $weekStart->copy()->addDays(6)->toDateString(),
            ])
            ->orderBy('date')
            ->get()
            ->map(fn (EmployeeShift $row) => [
                'id' => $row->id,
                'date' => $row->date?->toDateString(),
                'employee_name' => $row->employee?->full_name,
                'shift_name' => $row->shift?->name,
                'start_time' => substr((string) $row->shift?->start_time, 0, 5),
                'end_time' => substr((string) $row->shift?->end_time, 0, 5),
            ]);

        return Inertia::render('Shifts/Calendar', [
            'weekStart' => $weekStart->toDateString(),
            'assignments' => $assignments,
        ]);
    }
}
