<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\VendorEmployee;
use App\Models\VendorInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class VendorBillingController extends Controller
{
    /**
     * Nominal commercial rate (IDR) per outsourced head per month used to
     * suggest an invoice amount. This is pure commercial billing prep — NOT a
     * statutory payroll/labor figure (see billing disclaimer in the UI).
     */
    private const SUGGESTED_MONTHLY_RATE_PER_HEAD = 5_000_000;

    public function index(Request $request): Response
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $placements = VendorEmployee::query()
            ->with(['vendor:id,name', 'employee:id'])
            ->get()
            ->groupBy('vendor_id');

        $employeeIds = VendorEmployee::query()->pluck('employee_id');

        $attendanceByEmployee = Attendance::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('clock_in', [$periodStart, $periodEnd])
            ->get()
            ->groupBy('employee_id');

        $lines = $placements->map(function ($rows, $vendorId) use ($attendanceByEmployee) {
            $vendor = $rows->first()?->vendor;
            $headcount = $rows->count();
            $hours = 0.0;

            foreach ($rows as $placement) {
                $records = $attendanceByEmployee->get($placement->employee_id, collect());
                foreach ($records as $attendance) {
                    if ($attendance->clock_in && $attendance->clock_out) {
                        $hours += $attendance->clock_in->diffInMinutes($attendance->clock_out) / 60;
                    }
                }
            }

            return [
                'vendor_id' => (int) $vendorId,
                'vendor_name' => $vendor?->name ?? 'Unknown',
                'headcount' => $headcount,
                'hours' => round($hours, 2),
                'suggested_amount' => $headcount * self::SUGGESTED_MONTHLY_RATE_PER_HEAD,
            ];
        })->values()->sortBy('vendor_name')->values();

        $invoices = VendorInvoice::query()
            ->with('vendor:id,name')
            ->latest()
            ->get()
            ->map(fn (VendorInvoice $invoice) => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'vendor_id' => $invoice->vendor_id,
                'vendor_name' => $invoice->vendor?->name,
                'period_start' => $invoice->period_start?->toDateString(),
                'period_end' => $invoice->period_end?->toDateString(),
                'amount' => (float) $invoice->amount,
                'status' => $invoice->status,
                'paid_at' => $invoice->paid_at?->toDateTimeString(),
            ]);

        return Inertia::render('Billing/VendorBilling/Index', [
            'lines' => $lines,
            'invoices' => $invoices,
            'filters' => ['month' => $month, 'year' => $year],
            'period_label' => $periodStart->format('F Y'),
            'vendors' => Company::query()->where('type', 'vendor')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vendor_id' => ['required', 'exists:org_companies,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:draft,issued,paid,overdue,cancelled'],
        ]);

        $vendor = Company::query()->findOrFail($data['vendor_id']);
        abort_unless($vendor->type === 'vendor', 422);

        VendorInvoice::query()->create([
            'vendor_id' => $data['vendor_id'],
            'invoice_number' => $this->generateInvoiceNumber($data['vendor_id'], Carbon::parse($data['period_start'])),
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'amount' => $data['amount'],
            'status' => $data['status'] ?? 'issued',
        ]);

        return redirect()->route('vendor-billing.index')->with('success', 'Vendor invoice generated.');
    }

    public function update(Request $request, VendorInvoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:draft,issued,paid,overdue,cancelled'],
        ]);

        $invoice->update([
            'status' => $data['status'],
            'paid_at' => $data['status'] === 'paid' ? ($invoice->paid_at ?? now()) : null,
        ]);

        return redirect()->route('vendor-billing.index')->with('success', 'Invoice updated.');
    }

    public function markPaid(VendorInvoice $invoice): RedirectResponse
    {
        $invoice->update(['status' => 'paid', 'paid_at' => now()]);

        return redirect()->route('vendor-billing.index')->with('success', 'Invoice marked as paid.');
    }

    private function generateInvoiceNumber(int $vendorId, Carbon $periodStart): string
    {
        $base = sprintf('INV-%s-%03d', $periodStart->format('Ym'), $vendorId);
        $suffix = 1;
        $number = $base;

        while (VendorInvoice::query()->where('invoice_number', $number)->exists()) {
            $suffix++;
            $number = $base.'-'.$suffix;
        }

        return $number;
    }
}
