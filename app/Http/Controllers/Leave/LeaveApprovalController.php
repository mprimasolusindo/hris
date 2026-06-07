<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveApprovalController extends Controller
{
    public function index(): Response
    {
        $pending = Leave::query()
            ->with('employee:id,full_name,employee_code')
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get()
            ->map(fn (Leave $leave) => [
                'id' => $leave->id,
                'employee_name' => $leave->employee?->full_name,
                'employee_code' => $leave->employee?->employee_code,
                'type' => $leave->type,
                'start_date' => $leave->start_date?->toDateString(),
                'end_date' => $leave->end_date?->toDateString(),
                'created_at' => $leave->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('Leave/Approvals', [
            'pending' => $pending,
        ]);
    }

    public function decide(Request $request, Leave $leave): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
        ]);

        if ($leave->status !== 'pending') {
            return redirect()
                ->route('leave.approvals.index')
                ->with('success', 'Leave is no longer pending.');
        }

        $leave->update(['status' => $data['decision']]);

        return redirect()
            ->route('leave.approvals.index')
            ->with('success', 'Leave '.$data['decision'].'.');
    }
}
