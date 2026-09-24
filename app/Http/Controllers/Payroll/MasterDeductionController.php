<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\SalaryComponent;
use App\Support\ListPaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MasterDeductionController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = ListPaginator::resolvePerPage($request);

        return Inertia::render('Payroll/MasterDeductions/Index', [
            'items' => ListPaginator::paginate(
                SalaryComponent::query()
                    ->where('type', 'deduction')
                    ->orderBy('name')
                    ->select(['id', 'name', 'type', 'is_taxable']),
                $request,
            ),
            'filters' => [
                'per_page' => is_string($perPage) ? $perPage : (string) $perPage,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        SalaryComponent::query()->create($data);

        return redirect()
            ->route('payroll.master-deductions.index')
            ->with('success', 'Master deduction created.');
    }

    public function update(Request $request, SalaryComponent $salaryComponent): RedirectResponse
    {
        abort_unless($salaryComponent->type === 'deduction', 404);
        $salaryComponent->update($this->validated($request));

        return redirect()
            ->route('payroll.master-deductions.index')
            ->with('success', 'Master deduction updated.');
    }

    public function destroy(SalaryComponent $salaryComponent): RedirectResponse
    {
        abort_unless($salaryComponent->type === 'deduction', 404);
        $salaryComponent->delete();

        return redirect()
            ->route('payroll.master-deductions.index')
            ->with('success', 'Master deduction deleted.');
    }

    private function validated(Request $request): array
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_taxable' => ['boolean'],
        ]);

        return [
            'name' => $request->string('name')->toString(),
            'type' => 'deduction',
            'is_taxable' => $request->boolean('is_taxable'),
        ];
    }
}
