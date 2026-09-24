<?php

namespace App\Http\Controllers\WorkSchedule;

use App\Http\Controllers\Controller;
use App\Models\WorkSchedule;
use App\Support\ListPaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class WorkScheduleController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = ListPaginator::resolvePerPage($request);
        $schedules = ListPaginator::paginate(
            WorkSchedule::query()->orderByDesc('is_default')->orderBy('name'),
            $request,
        );
        $schedules->getCollection()->transform(fn (WorkSchedule $schedule) => $this->serialize($schedule));

        return Inertia::render('WorkSchedules/Index', [
            'schedules' => $schedules,
            'filters' => ['per_page' => (string) $perPage],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            if ($data['is_default']) {
                WorkSchedule::query()->where('is_default', true)->update(['is_default' => false]);
            }

            WorkSchedule::query()->create($data);
        });

        return redirect()->route('work-schedules.index')->with('success', 'Work schedule created.');
    }

    public function update(Request $request, WorkSchedule $workSchedule): RedirectResponse
    {
        $data = $this->validated($request, $workSchedule);

        DB::transaction(function () use ($data, $workSchedule) {
            if ($data['is_default']) {
                WorkSchedule::query()
                    ->where('is_default', true)
                    ->where('id', '!=', $workSchedule->id)
                    ->update(['is_default' => false]);
            }

            $workSchedule->update($data);
        });

        return redirect()->route('work-schedules.index')->with('success', 'Work schedule updated.');
    }

    public function destroy(WorkSchedule $workSchedule): RedirectResponse
    {
        if ($workSchedule->is_default) {
            return redirect()
                ->route('work-schedules.index')
                ->with('error', 'Cannot delete the default work schedule. Set another schedule as default first.');
        }

        $workSchedule->delete();

        return redirect()->route('work-schedules.index')->with('success', 'Work schedule deleted.');
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     code: string,
     *     is_default: bool,
     *     default_start_time: string|null,
     *     default_end_time: string|null,
     *     mon_working: bool,
     *     tue_working: bool,
     *     wed_working: bool,
     *     thu_working: bool,
     *     fri_working: bool,
     *     sat_working: bool,
     *     sun_working: bool,
     *     working_days_label: string
     * }
     */
    private function serialize(WorkSchedule $schedule): array
    {
        $days = [];
        if ($schedule->mon_working) {
            $days[] = 'Mon';
        }
        if ($schedule->tue_working) {
            $days[] = 'Tue';
        }
        if ($schedule->wed_working) {
            $days[] = 'Wed';
        }
        if ($schedule->thu_working) {
            $days[] = 'Thu';
        }
        if ($schedule->fri_working) {
            $days[] = 'Fri';
        }
        if ($schedule->sat_working) {
            $days[] = 'Sat';
        }
        if ($schedule->sun_working) {
            $days[] = 'Sun';
        }

        return [
            'id' => $schedule->id,
            'name' => $schedule->name,
            'code' => $schedule->code,
            'is_default' => (bool) $schedule->is_default,
            'default_start_time' => $schedule->default_start_time
                ? substr((string) $schedule->default_start_time, 0, 5)
                : null,
            'default_end_time' => $schedule->default_end_time
                ? substr((string) $schedule->default_end_time, 0, 5)
                : null,
            'mon_working' => (bool) $schedule->mon_working,
            'tue_working' => (bool) $schedule->tue_working,
            'wed_working' => (bool) $schedule->wed_working,
            'thu_working' => (bool) $schedule->thu_working,
            'fri_working' => (bool) $schedule->fri_working,
            'sat_working' => (bool) $schedule->sat_working,
            'sun_working' => (bool) $schedule->sun_working,
            'working_days_label' => $days === [] ? '—' : implode(', ', $days),
        ];
    }

    /**
     * @return array{
     *     name: string,
     *     code: string,
     *     is_default: bool,
     *     default_start_time: string|null,
     *     default_end_time: string|null,
     *     mon_working: bool,
     *     tue_working: bool,
     *     wed_working: bool,
     *     thu_working: bool,
     *     fri_working: bool,
     *     sat_working: bool,
     *     sun_working: bool
     * }
     */
    private function validated(Request $request, ?WorkSchedule $schedule = null): array
    {
        $request->merge([
            'default_start_time' => $request->filled('default_start_time')
                ? $request->input('default_start_time')
                : null,
            'default_end_time' => $request->filled('default_end_time')
                ? $request->input('default_end_time')
                : null,
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:32',
                Rule::unique('att_work_schedules', 'code')->ignore($schedule?->id),
            ],
            'is_default' => ['boolean'],
            'default_start_time' => ['nullable', 'date_format:H:i'],
            'default_end_time' => ['nullable', 'date_format:H:i'],
            'mon_working' => ['boolean'],
            'tue_working' => ['boolean'],
            'wed_working' => ['boolean'],
            'thu_working' => ['boolean'],
            'fri_working' => ['boolean'],
            'sat_working' => ['boolean'],
            'sun_working' => ['boolean'],
        ]);

        $start = $data['default_start_time'] ?? null;
        $end = $data['default_end_time'] ?? null;

        if ((blank($start) && filled($end)) || (filled($start) && blank($end))) {
            throw ValidationException::withMessages([
                'default_start_time' => 'Start and end times must both be set or both left empty.',
                'default_end_time' => 'Start and end times must both be set or both left empty.',
            ]);
        }

        return [
            'name' => $data['name'],
            'code' => $data['code'],
            'is_default' => $request->boolean('is_default'),
            'default_start_time' => filled($start) ? $start.':00' : null,
            'default_end_time' => filled($end) ? $end.':00' : null,
            'mon_working' => $request->boolean('mon_working'),
            'tue_working' => $request->boolean('tue_working'),
            'wed_working' => $request->boolean('wed_working'),
            'thu_working' => $request->boolean('thu_working'),
            'fri_working' => $request->boolean('fri_working'),
            'sat_working' => $request->boolean('sat_working'),
            'sun_working' => $request->boolean('sun_working'),
        ];
    }
}
