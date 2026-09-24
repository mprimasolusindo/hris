<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Leave;
use App\Support\ListPaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveApprovalController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = ListPaginator::resolvePerPage($request);
        $pending = ListPaginator::paginate(
            Leave::query()
                ->with('employee:id,full_name,employee_code')
                ->where('status', 'pending')
                ->orderBy('created_at'),
            $request,
        );
        $pending->getCollection()->transform(fn (Leave $leave) => [
            'id' => $leave->id,
            'employee_name' => $leave->employee?->full_name,
            'employee_code' => $leave->employee?->employee_code,
            'type' => $leave->type,
            'start_date' => $leave->start_date?->toDateString(),
            'end_date' => $leave->end_date?->toDateString(),
            'reason' => $leave->reason,
            'created_at' => $leave->created_at?->toDateTimeString(),
        ]);

        return Inertia::render('Leave/Approvals', [
            'pending' => $pending,
            'filters' => ['per_page' => (string) $perPage],
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

        $approverId = Employee::query()
            ->where('user_id', auth()->id())
            ->value('id');

        $leave->update([
            'status' => $data['decision'],
            'approved_by' => $approverId,
            'decided_at' => now(),
        ]);

        return redirect()
            ->route('leave.approvals.index')
            ->with('success', 'Leave '.$data['decision'].'.');
    }
}
