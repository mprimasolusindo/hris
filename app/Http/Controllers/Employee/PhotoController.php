<?php

namespace App\Http\Controllers\Employee;

use App\Actions\Employee\UploadEmployeePhotoAction;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PhotoController extends Controller
{
    public function store(Request $request, Employee $employee, UploadEmployeePhotoAction $upload): RedirectResponse
    {
        $this->authorize('update', $employee);
        $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
        ]);

        $upload($employee, $request->file('photo'));

        return back()->with('success', 'Profile photo updated.');
    }
}
