<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\TaxRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin CRUD for cfg_tax_rules — PPh21 TER brackets (PMK 168/2023), PTKP
 * thresholds and Pasal 17 rates. Source values via the HR research skill.
 */
class TaxRuleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Payroll/TaxRules/Index', [
            'items' => TaxRule::query()
                ->orderBy('rule_type')
                ->orderBy('ptkp_category')
                ->orderBy('gross_min')
                ->get(['id', 'name', 'rule_type', 'ptkp_category', 'gross_min', 'gross_max', 'value']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        TaxRule::query()->create($this->validated($request));

        return redirect()
            ->route('payroll.tax-rules.index')
            ->with('success', 'Tax rule created.');
    }

    public function update(Request $request, TaxRule $taxRule): RedirectResponse
    {
        $taxRule->update($this->validated($request, $taxRule->id));

        return redirect()
            ->route('payroll.tax-rules.index')
            ->with('success', 'Tax rule updated.');
    }

    public function destroy(TaxRule $taxRule): RedirectResponse
    {
        $taxRule->delete();

        return redirect()
            ->route('payroll.tax-rules.index')
            ->with('success', 'Tax rule deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cfg_tax_rules', 'name')->ignore($ignoreId),
            ],
            'rule_type' => ['nullable', 'string', 'max:255'],
            'ptkp_category' => ['nullable', 'string', 'max:8'],
            'gross_min' => ['nullable', 'numeric', 'min:0'],
            'gross_max' => ['nullable', 'numeric', 'min:0'],
            'value' => ['required', 'numeric', 'min:0'],
        ]);

        return [
            'name' => $request->string('name')->toString(),
            'rule_type' => $request->filled('rule_type') ? $request->string('rule_type')->toString() : null,
            'ptkp_category' => $request->filled('ptkp_category') ? $request->string('ptkp_category')->toString() : null,
            'gross_min' => $request->filled('gross_min') ? (float) $request->input('gross_min') : null,
            'gross_max' => $request->filled('gross_max') ? (float) $request->input('gross_max') : null,
            'value' => (float) $request->input('value'),
        ];
    }
}
