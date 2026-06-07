<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PerformanceReview;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PerformanceReviewController extends Controller
{
    private const STATUSES = ['draft', 'submitted', 'acknowledged', 'finalized'];

    public function index(): Response
    {
        $reviews = PerformanceReview::query()
            ->with(['employee:id,full_name,employee_code', 'reviewer:id,name'])
            ->latest()
            ->get()
            ->map(fn (PerformanceReview $review) => [
                'id' => $review->id,
                'employee_id' => $review->employee_id,
                'employee_name' => $review->employee?->full_name,
                'reviewer_id' => $review->reviewer_id,
                'reviewer_name' => $review->reviewer?->name,
                'period_year' => $review->period_year,
                'period_quarter' => $review->period_quarter,
                'period' => $review->period_year.' Q'.$review->period_quarter,
                'rating' => (string) $review->rating,
                'goals' => $review->goals,
                'notes' => $review->notes,
                'status' => $review->status,
            ]);

        return Inertia::render('Talent/Performance/Index', [
            'items' => $reviews,
            'employees' => $this->employeeOptions(),
            'reviewers' => User::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => self::STATUSES,
            'summary' => [
                'total' => $reviews->count(),
                'finalized' => $reviews->where('status', 'finalized')->count(),
                'averageRating' => round((float) $reviews->avg(fn ($r) => (float) $r['rating']), 2),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        PerformanceReview::query()->create($this->validated($request));

        return redirect()
            ->route('performance.index')
            ->with('success', 'Performance review created.');
    }

    public function update(Request $request, PerformanceReview $performanceReview): RedirectResponse
    {
        $performanceReview->update($this->validated($request));

        return redirect()
            ->route('performance.index')
            ->with('success', 'Performance review updated.');
    }

    public function destroy(PerformanceReview $performanceReview): RedirectResponse
    {
        $performanceReview->delete();

        return redirect()
            ->route('performance.index')
            ->with('success', 'Performance review deleted.');
    }

    private function validated(Request $request): array
    {
        if (in_array($request->input('reviewer_id'), ['none', ''], true)) {
            $request->merge(['reviewer_id' => null]);
        }

        return $request->validate([
            'employee_id' => ['required', 'exists:emp_employees,id'],
            'reviewer_id' => ['nullable', 'exists:users,id'],
            'period_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'period_quarter' => ['required', 'integer', 'min:1', 'max:4'],
            'rating' => ['required', 'numeric', 'min:0', 'max:5'],
            'goals' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:'.implode(',', self::STATUSES)],
        ]);
    }

    private function employeeOptions()
    {
        return Employee::query()
            ->orderBy('full_name')
            ->get(['id', 'full_name'])
            ->map(fn (Employee $e) => ['id' => $e->id, 'name' => $e->full_name]);
    }
}
