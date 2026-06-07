<?php

namespace App\Http\Requests\BugReport;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBugReportSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
        ];
    }
}
