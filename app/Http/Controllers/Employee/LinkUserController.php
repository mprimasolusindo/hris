<?php

namespace App\Http\Controllers\Employee;

use App\Actions\Employee\LinkEmployeeUserAction;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LinkUserController extends Controller
{
    public function store(Request $request, Employee $employee, LinkEmployeeUserAction $link): RedirectResponse
    {
        $this->authorize('update', $employee);
        $data = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $userId = isset($data['user_id']) && $data['user_id'] !== ''
            ? (int) $data['user_id']
            : null;
        $link($employee, $userId);

        return back()->with('success', 'User link updated.');
    }
}
