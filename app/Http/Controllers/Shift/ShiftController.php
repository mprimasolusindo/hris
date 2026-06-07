<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\Controller;
use App\Models\EmployeeShift;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShiftController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Shifts/Index', [
            'shifts' => Shift::query()
                ->orderBy('name')
                ->get(['id', 'name', 'start_time', 'end_time'])
                ->map(fn (Shift $shift) => [
                    'id' => $shift->id,
                    'name' => $shift->name,
                    'start_time' => substr((string) $shift->start_time, 0, 5),
                    'end_time' => substr((string) $shift->end_time, 0, 5),
                ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
        ]);

        Shift::query()->create([
            'name' => $data['name'],
            'start_time' => $data['start_time'].':00',
            'end_time' => $data['end_time'].':00',
        ]);

        return redirect()->route('shifts.index')->with('success', 'Shift created.');
    }

    public function show(Shift $shift): Response
    {
        $shift->load(['employeeShifts.employee:id,full_name,employee_code']);

        return Inertia::render('Shifts/Show', [
            'shift' => [
                'id' => $shift->id,
                'name' => $shift->name,
                'start_time' => substr((string) $shift->start_time, 0, 5),
                'end_time' => substr((string) $shift->end_time, 0, 5),
            ],
            'assignments' => $shift->employeeShifts
                ->sortByDesc('date')
                ->take(50)
                ->map(fn (EmployeeShift $row) => [
                    'id' => $row->id,
                    'date' => $row->date?->toDateString(),
                    'employee_name' => $row->employee?->full_name,
                    'employee_code' => $row->employee?->employee_code,
                ])
                ->values(),
        ]);
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
        ]);

        $shift->update([
            'name' => $data['name'],
            'start_time' => $data['start_time'].':00',
            'end_time' => $data['end_time'].':00',
        ]);

        return redirect()->route('shifts.index')->with('success', 'Shift updated.');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        $shift->delete();

        return redirect()->route('shifts.index')->with('success', 'Shift deleted.');
    }
}
