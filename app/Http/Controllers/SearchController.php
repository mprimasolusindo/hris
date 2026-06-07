<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Employee;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function index(Request $request): Response
    {
        $query = trim((string) $request->query('q', ''));

        $employees = [];
        $jobs = [];
        $candidates = [];

        if ($query !== '') {
            $like = '%'.$query.'%';

            $employees = Employee::query()
                ->where(function ($q) use ($like) {
                    $q->where('full_name', 'like', $like)
                        ->orWhere('employee_code', 'like', $like)
                        ->orWhere('email', 'like', $like);
                })
                ->orderBy('full_name')
                ->limit(20)
                ->get(['id', 'full_name', 'employee_code', 'email', 'status'])
                ->map(fn (Employee $e) => [
                    'id' => $e->id,
                    'title' => $e->full_name,
                    'subtitle' => $e->employee_code,
                    'meta' => $e->email,
                    'url' => route('employees.show', $e->id),
                ]);

            $jobs = JobPosting::query()
                ->where('title', 'like', $like)
                ->orderBy('title')
                ->limit(20)
                ->get(['id', 'title', 'status'])
                ->map(fn (JobPosting $j) => [
                    'id' => $j->id,
                    'title' => $j->title,
                    'subtitle' => $j->status,
                    'meta' => null,
                    'url' => route('recruitment.jobs.show', $j->id),
                ]);

            $candidates = Candidate::query()
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like);
                })
                ->orderBy('name')
                ->limit(20)
                ->get(['id', 'name', 'email', 'phone'])
                ->map(fn (Candidate $c) => [
                    'id' => $c->id,
                    'title' => $c->name,
                    'subtitle' => $c->email,
                    'meta' => $c->phone,
                    'url' => route('recruitment.candidates.show', $c->id),
                ]);
        }

        return Inertia::render('Search/Index', [
            'query' => $query,
            'results' => [
                'employees' => $employees,
                'jobs' => $jobs,
                'candidates' => $candidates,
            ],
            'total' => count($employees) + count($jobs) + count($candidates),
        ]);
    }
}
