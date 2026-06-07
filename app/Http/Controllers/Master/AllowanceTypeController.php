<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\SalaryComponent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AllowanceTypeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Master/AllowanceTypes/Index', [
            'items' => $this->queryEarnings(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        SalaryComponent::query()->create($this->validated($request));

        return redirect()
            ->route('master.allowance-types.index')
            ->with('success', 'Allowance type created.');
    }

    public function update(Request $request, SalaryComponent $salaryComponent): RedirectResponse
    {
        abort_unless($salaryComponent->type === 'earning', 404);
        $salaryComponent->update($this->validated($request));

        return redirect()
            ->route('master.allowance-types.index')
            ->with('success', 'Allowance type updated.');
    }

    public function destroy(SalaryComponent $salaryComponent): RedirectResponse
    {
        abort_unless($salaryComponent->type === 'earning', 404);
        $salaryComponent->delete();

        return redirect()
            ->route('master.allowance-types.index')
            ->with('success', 'Allowance type deleted.');
    }

    private function validated(Request $request): array
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_taxable' => ['boolean'],
        ]);

        return [
            'name' => $request->string('name')->toString(),
            'type' => 'earning',
            'is_taxable' => $request->boolean('is_taxable'),
        ];
    }

    private function queryEarnings()
    {
        return SalaryComponent::query()
            ->where('type', 'earning')
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'is_taxable']);
    }
}
