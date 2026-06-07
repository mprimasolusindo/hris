<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PositionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Organization/Positions/Index', [
            'positions' => Position::query()
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Position::query()->create($data);

        return redirect()
            ->route('organization.positions.index')
            ->with('success', 'Position created.');
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $position->update($data);

        return redirect()
            ->route('organization.positions.index')
            ->with('success', 'Position updated.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        $position->delete();

        return redirect()
            ->route('organization.positions.index')
            ->with('success', 'Position deleted.');
    }
}
