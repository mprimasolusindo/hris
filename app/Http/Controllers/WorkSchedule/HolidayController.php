<?php

namespace App\Http\Controllers\WorkSchedule;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Support\ListPaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class HolidayController extends Controller
{
    public function index(Request $request): Response
    {
        $year = (int) $request->input('year', now()->year);
        $perPage = ListPaginator::resolvePerPage($request);

        return Inertia::render('Holidays/Index', [
            'year' => $year,
            'holidays' => ListPaginator::paginate(
                Holiday::query()->whereYear('holiday_date', $year)->orderByDesc('holiday_date'),
                $request,
            )->through(fn (Holiday $holiday) => $this->serialize($holiday)),
            'typeOptions' => [
                ['value' => 'national', 'label' => 'National'],
                ['value' => 'company', 'label' => 'Company'],
                ['value' => 'joint_leave', 'label' => 'Joint leave (cuti bersama)'],
            ],
            'filters' => [
                'per_page' => is_string($perPage) ? $perPage : (string) $perPage,
                'year' => $year,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Holiday::query()->create($data);

        return redirect()
            ->route('holidays.index', ['year' => substr($data['holiday_date'], 0, 4)])
            ->with('success', 'Holiday created.');
    }

    public function update(Request $request, Holiday $holiday): RedirectResponse
    {
        $data = $this->validated($request, $holiday);

        $holiday->update($data);

        return redirect()
            ->route('holidays.index', ['year' => substr($data['holiday_date'], 0, 4)])
            ->with('success', 'Holiday updated.');
    }

    public function destroy(Holiday $holiday): RedirectResponse
    {
        $year = $holiday->holiday_date?->year ?? now()->year;
        $holiday->delete();

        return redirect()
            ->route('holidays.index', ['year' => $year])
            ->with('success', 'Holiday deleted.');
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     holiday_date: string,
     *     type: string,
     *     is_half_day: bool,
     *     notes: string|null
     * }
     */
    private function serialize(Holiday $holiday): array
    {
        return [
            'id' => $holiday->id,
            'name' => $holiday->name,
            'holiday_date' => $holiday->holiday_date?->toDateString() ?? '',
            'type' => $holiday->type,
            'is_half_day' => (bool) $holiday->is_half_day,
            'notes' => $holiday->notes,
        ];
    }

    /**
     * @return array{
     *     name: string,
     *     holiday_date: string,
     *     type: string,
     *     is_half_day: bool,
     *     notes: string|null
     * }
     */
    private function validated(Request $request, ?Holiday $holiday = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'holiday_date' => ['required', 'date'],
            'type' => ['required', 'string', Rule::in(['national', 'company', 'joint_leave'])],
            'is_half_day' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $date = \Illuminate\Support\Carbon::parse($data['holiday_date'])->toDateString();

        $duplicate = Holiday::query()
            ->whereDate('holiday_date', $date)
            ->when($holiday !== null, fn ($q) => $q->where('id', '!=', $holiday->id))
            ->exists();

        if ($duplicate) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'holiday_date' => 'A holiday already exists on this date.',
            ]);
        }

        return [
            'name' => $data['name'],
            'holiday_date' => $date,
            'type' => $data['type'],
            'is_half_day' => $request->boolean('is_half_day'),
            'notes' => $data['notes'] ?? null,
        ];
    }
}
