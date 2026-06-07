<?php

namespace App\Http\Controllers\Employee;

use App\Actions\Employee\ExportEmployeesAction;
use App\Actions\Employee\ImportEmployeesAction;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportExportController extends Controller
{
    public function import(Request $request, ImportEmployeesAction $import): RedirectResponse
    {
        $this->authorize('import', Employee::class);

        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
            'dry_run' => ['boolean'],
        ]);

        $result = $import($data['file'], (bool) ($data['dry_run'] ?? false));

        return back()->with([
            'success' => "Import complete: {$result['imported']} row(s) processed.",
            'import_errors' => $result['errors'],
        ]);
    }

    public function export(Request $request, ExportEmployeesAction $export): StreamedResponse
    {
        $this->authorize('export', Employee::class);

        $ids = $request->query('ids');
        $parsed = is_string($ids) ? array_filter(array_map('intval', explode(',', $ids))) : null;

        return $export($parsed ?: null);
    }
}
