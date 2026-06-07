<?php

namespace App\Services\Employee;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Services\Employee\Concerns\ManagesEmployeeSubResource;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentService
{
    use ManagesEmployeeSubResource;

    protected function modelClass(): string
    {
        return EmployeeDocument::class;
    }

    public function storeUpload(Employee $employee, UploadedFile $file, string $category, ?int $uploadedBy): EmployeeDocument
    {
        $path = $file->store("employees/{$employee->id}/documents", 'public');

        return EmployeeDocument::query()->create([
            'employee_id' => $employee->id,
            'category' => $category,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $uploadedBy,
        ]);
    }

    public function deleteWithFile(EmployeeDocument $document): void
    {
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();
    }
}
