<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillingPayment;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingPaymentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Saas/Payments/Index', [
            'payments' => BillingPayment::query()
                ->with('tenant:id,name')
                ->latest()
                ->get()
                ->map(fn (BillingPayment $payment) => [
                    'id' => $payment->id,
                    'tenant_id' => $payment->tenant_id,
                    'tenant_name' => $payment->tenant?->name,
                    'amount' => (float) $payment->amount,
                    'method' => $payment->method,
                    'status' => $payment->status,
                    'paid_at' => $payment->paid_at?->toDateTimeString(),
                ]),
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['paid_at'] = $data['status'] === 'paid' ? now() : null;

        BillingPayment::query()->create($data);

        return redirect()->route('admin.saas.payments.index')->with('success', 'Payment recorded.');
    }

    public function update(Request $request, BillingPayment $payment): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['paid_at'] = $data['status'] === 'paid' ? ($payment->paid_at ?? now()) : null;

        $payment->update($data);

        return redirect()->route('admin.saas.payments.index')->with('success', 'Payment updated.');
    }

    public function destroy(BillingPayment $payment): RedirectResponse
    {
        $payment->delete();

        return redirect()->route('admin.saas.payments.index')->with('success', 'Payment deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'tenant_id' => ['required', 'exists:sys_tenants,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'method' => ['nullable', 'string', 'max:64'],
            'status' => ['required', 'in:pending,paid,failed,refunded'],
        ]);
    }
}
