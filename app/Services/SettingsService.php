<?php

namespace App\Services;

use App\Models\Setting;

class SettingsService
{
    /**
     * @param  mixed  $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Setting::query()->where('key', $key)->first();

        if ($setting === null || $setting->value === null) {
            return $default;
        }

        return $this->castValue($setting->value);
    }

    public function set(string $key, mixed $value): void
    {
        $stored = is_bool($value) ? ($value ? '1' : '0') : (string) $value;

        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $stored],
        );
    }

    private function castValue(string $value): mixed
    {
        if ($value === '1') {
            return true;
        }

        if ($value === '0') {
            return false;
        }

        return $value;
    }
}
