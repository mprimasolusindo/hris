<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class ListPaginator
{
    public const OPTIONS = [10, 20, 30, 50, 100];

    public static function resolvePerPage(Request $request, int $default = 20): int|string
    {
        $raw = $request->query('per_page', (string) $default);
        if (! is_scalar($raw)) {
            return $default;
        }
        $value = (string) $raw;
        if ($value === 'all') {
            return 'all';
        }
        $n = (int) $value;

        return in_array($n, self::OPTIONS, true) ? $n : $default;
    }

    public static function paginate(EloquentBuilder|QueryBuilder $query, Request $request, int $default = 20): LengthAwarePaginatorContract
    {
        $perPage = self::resolvePerPage($request, $default);
        if ($perPage === 'all') {
            $items = $query->get();

            return self::makePaginator($items, $items->count(), max($items->count(), 1), 1, $request);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public static function paginateCollection(Collection|array $items, Request $request, int $default = 20): LengthAwarePaginatorContract
    {
        $collection = $items instanceof Collection ? $items->values() : collect($items)->values();
        $perPage = self::resolvePerPage($request, $default);
        $total = $collection->count();
        if ($perPage === 'all') {
            return self::makePaginator($collection, $total, max($total, 1), 1, $request);
        }
        $page = max(1, (int) $request->query('page', 1));
        $slice = $collection->forPage($page, $perPage)->values();

        return self::makePaginator($slice, $total, $perPage, $page, $request);
    }

    private static function makePaginator($items, int $total, int $perPage, int $page, Request $request): LengthAwarePaginator
    {
        return (new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]))->withQueryString();
    }
}
