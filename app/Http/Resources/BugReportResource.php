<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BugReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'url' => $this->url,
            'page_title' => $this->page_title,
            'console_log' => $this->console_log ?? [],
            'user_agent' => $this->user_agent,
            'viewport_width' => $this->viewport_width,
            'viewport_height' => $this->viewport_height,
            'screenshot_url' => $this->screenshot_path
                ? asset('storage/'.$this->screenshot_path)
                : null,
            'reported_by_name' => $this->reporter?->name,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
