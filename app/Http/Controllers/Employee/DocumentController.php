<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Services\Employee\EmployeeDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(private EmployeeDocumentService $service) {}

    public function store(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);
        $data = $request->validate([
            'category' => ['required', 'string', 'in:ktp,npwp,contract,certificate,other'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $this->service->storeUpload(
            $employee,
            $data['file'],
            $data['category'],
            $request->user()?->id
        );

        return back()->with('success', 'Document uploaded.');
    }

    public function destroy(Employee $employee, EmployeeDocument $document): RedirectResponse
    {
        $this->authorize('update', $employee);
        abort_unless($document->employee_id === $employee->id, 404);
        $this->service->deleteWithFile($document);

        return back()->with('success', 'Document removed.');
    }
}
