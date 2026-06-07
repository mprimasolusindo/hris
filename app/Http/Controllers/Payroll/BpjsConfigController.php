<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\BpjsConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin CRUD for cfg_bpjs iuran percentages (employee + employer share per
 * BPJS program). Source rates via the HR research skill, never hard-code.
 */
class BpjsConfigController extends Controller
{
    private const TYPES = ['kesehatan', 'jht', 'jp', 'jkk', 'jkm', 'jkp'];

    public function index(): Response
    {
        return Inertia::render('Payroll/BpjsConfig/Index', [
            'items' => BpjsConfig::query()
                ->orderBy('type')
                ->get(['id', 'type', 'employee_percentage', 'company_percentage']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        BpjsConfig::query()->create($this->validated($request));

        return redirect()
            ->route('payroll.bpjs-config.index')
            ->with('success', 'BPJS configuration created.');
    }

    public function update(Request $request, BpjsConfig $bpjsConfig): RedirectResponse
    {
        $bpjsConfig->update($this->validated($request, $bpjsConfig->id));

        return redirect()
            ->route('payroll.bpjs-config.index')
            ->with('success', 'BPJS configuration updated.');
    }

    public function destroy(BpjsConfig $bpjsConfig): RedirectResponse
    {
        $bpjsConfig->delete();

        return redirect()
            ->route('payroll.bpjs-config.index')
            ->with('success', 'BPJS configuration deleted.');
    }

    /**
     * @return array{type: string, employee_percentage: float, company_percentage: float}
     */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $request->validate([
            'type' => [
                'required',
                'string',
                Rule::in(self::TYPES),
                Rule::unique('cfg_bpjs', 'type')->ignore($ignoreId),
            ],
            'employee_percentage' => ['required', 'numeric', 'min:0', 'max:1'],
            'company_percentage' => ['required', 'numeric', 'min:0', 'max:1'],
        ]);

        return [
            'type' => $request->string('type')->toString(),
            'employee_percentage' => (float) $request->input('employee_percentage'),
            'company_percentage' => (float) $request->input('company_percentage'),
        ];
    }
}
