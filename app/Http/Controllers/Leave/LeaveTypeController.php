<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LeaveTypeController extends Controller
{
    public function index(): Response
    {
        $types = LeaveType::query()
            ->orderBy('name')
            ->get()
            ->map(fn (LeaveType $type) => $this->serialize($type));

        return Inertia::render('Leave/Types', [
            'types' => $types,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:32', 'unique:lv_leave_types,code'],
            'name' => ['required', 'string', 'max:255'],
            'annual_entitlement_days' => ['required', 'integer', 'min:0', 'max:365'],
            'is_paid' => ['boolean'],
        ]);

        LeaveType::query()->create($data);

        return redirect()->route('leave.types.index')->with('success', 'Leave type created.');
    }

    public function update(Request $request, LeaveType $leaveType): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:32', Rule::unique('lv_leave_types', 'code')->ignore($leaveType->id)],
            'name' => ['required', 'string', 'max:255'],
            'annual_entitlement_days' => ['required', 'integer', 'min:0', 'max:365'],
            'is_paid' => ['boolean'],
        ]);

        $leaveType->update($data);

        return redirect()->route('leave.types.index')->with('success', 'Leave type updated.');
    }

    public function destroy(LeaveType $leaveType): RedirectResponse
    {
        $leaveType->delete();

        return redirect()->route('leave.types.index')->with('success', 'Leave type removed.');
    }

    private function serialize(LeaveType $type): array
    {
        return [
            'id' => $type->id,
            'code' => $type->code,
            'name' => $type->name,
            'annual_entitlement_days' => $type->annual_entitlement_days,
            'is_paid' => $type->is_paid,
        ];
    }
}
