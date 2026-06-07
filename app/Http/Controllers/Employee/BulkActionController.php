<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BulkActionController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $this->authorize('bulkUpdate', Employee::class);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:emp_employees,id'],
            'action' => ['required', 'string', 'in:archive,set_status'],
            'status' => ['required_if:action,set_status', 'string', 'max:32'],
        ]);

        $query = Employee::query()->whereIn('id', $data['ids']);

        if ($data['action'] === 'archive') {
            $query->each(fn (Employee $e) => $e->delete());
        } else {
            $query->update(['status' => $data['status']]);
        }

        return back()->with('success', 'Bulk action completed.');
    }
}
