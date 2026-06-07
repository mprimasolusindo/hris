<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeTaxProfileRequest;
use App\Models\Employee;
use App\Services\Employee\EmployeeTaxProfileService;
use Illuminate\Http\RedirectResponse;

class TaxProfileController extends Controller
{
    public function __construct(private EmployeeTaxProfileService $service) {}

    public function store(StoreEmployeeTaxProfileRequest $request, Employee $employee): RedirectResponse
    {
        $this->service->upsertForEmployee($employee, $request->validated());

        return back()->with('success', 'Tax profile saved.');
    }
}
