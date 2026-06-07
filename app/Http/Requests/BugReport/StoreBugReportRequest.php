<?php

namespace App\Http\Requests\BugReport;

use Illuminate\Foundation\Http\FormRequest;

class StoreBugReportRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'screenshot' => ['required', 'image', 'max:5120'],
            'url' => ['required', 'string', 'max:2048'],
            'page_title' => ['nullable', 'string', 'max:255'],
            'console_log' => ['nullable', 'string'],
            'user_agent' => ['nullable', 'string', 'max:1024'],
            'viewport_width' => ['nullable', 'integer', 'min:1'],
            'viewport_height' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
