<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\SalaryComponent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MasterAllowanceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Payroll/MasterAllowances/Index', [
            'items' => SalaryComponent::query()
                ->where('type', 'earning')
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'is_taxable']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        SalaryComponent::query()->create($data);

        return redirect()
            ->route('payroll.master-allowances.index')
            ->with('success', 'Master allowance created.');
    }

    public function update(Request $request, SalaryComponent $salaryComponent): RedirectResponse
    {
        abort_unless($salaryComponent->type === 'earning', 404);
        $salaryComponent->update($this->validated($request));

        return redirect()
            ->route('payroll.master-allowances.index')
            ->with('success', 'Master allowance updated.');
    }

    public function destroy(SalaryComponent $salaryComponent): RedirectResponse
    {
        abort_unless($salaryComponent->type === 'earning', 404);
        $salaryComponent->delete();

        return redirect()
            ->route('payroll.master-allowances.index')
            ->with('success', 'Master allowance deleted.');
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
}
