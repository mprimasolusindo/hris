<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Support\ListPaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PositionController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = ListPaginator::resolvePerPage($request);

        return Inertia::render('Organization/Positions/Index', [
            'positions' => ListPaginator::paginate(
                Position::query()->orderBy('name')->select(['id', 'name']),
                $request,
            ),
            'filters' => [
                'per_page' => is_string($perPage) ? $perPage : (string) $perPage,
            ],
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
