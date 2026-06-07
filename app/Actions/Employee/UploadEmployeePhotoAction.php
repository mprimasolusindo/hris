<?php

namespace App\Actions\Employee;

use App\Models\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadEmployeePhotoAction
{
    public function __invoke(Employee $employee, UploadedFile $file): Employee
    {
        if ($employee->profile_photo_path) {
            Storage::disk('public')->delete($employee->profile_photo_path);
        }

        $path = $file->store("employees/{$employee->id}/photos", 'public');
        $employee->update(['profile_photo_path' => $path]);

        return $employee->fresh();
    }
}
