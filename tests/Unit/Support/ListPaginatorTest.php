<?php

namespace Tests\Unit\Support;

use App\Models\User;
use App\Support\ListPaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ListPaginatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_per_page_accepts_allowed_values(): void
    {
        $this->assertSame(20, ListPaginator::resolvePerPage(Request::create('/', 'GET', ['per_page' => '20'])));
        $this->assertSame('all', ListPaginator::resolvePerPage(Request::create('/', 'GET', ['per_page' => 'all'])));
        $this->assertSame(20, ListPaginator::resolvePerPage(Request::create('/', 'GET', ['per_page' => '999'])));
    }

    public function test_paginate_limits_results(): void
    {
        User::factory()->count(25)->create();
        $paginator = ListPaginator::paginate(User::query()->orderBy('id'), Request::create('/', 'GET', ['per_page' => '10']));
        $this->assertCount(10, $paginator->items());
        $this->assertSame(25, $paginator->total());
        $this->assertSame(3, $paginator->lastPage());
    }

    public function test_paginate_all_returns_single_page(): void
    {
        User::factory()->count(5)->create();
        $paginator = ListPaginator::paginate(User::query()->orderBy('id'), Request::create('/', 'GET', ['per_page' => 'all']));
        $this->assertCount(5, $paginator->items());
        $this->assertSame(1, $paginator->lastPage());
    }

    public function test_paginate_collection_slices_page(): void
    {
        $items = collect(range(1, 25))->map(fn ($n) => ['id' => $n]);
        $paginator = ListPaginator::paginateCollection($items, Request::create('/', 'GET', ['per_page' => '10', 'page' => 2]));
        $this->assertCount(10, $paginator->items());
        $this->assertSame(2, $paginator->currentPage());
        $this->assertSame(25, $paginator->total());
    }

    public function test_paginate_uses_named_page_query(): void
    {
        User::factory()->count(25)->create();
        $request = Request::create('/', 'GET', ['per_page' => '10', 'invoices_page' => 2]);
        $this->app->instance('request', $request);

        $paginator = ListPaginator::paginate(
            User::query()->orderBy('id'),
            $request,
            20,
            'invoices_page',
        );

        $this->assertCount(10, $paginator->items());
        $this->assertSame(2, $paginator->currentPage());
        $this->assertSame('invoices_page', $paginator->getPageName());
    }

    public function test_paginate_collection_uses_named_page_query(): void
    {
        $items = collect(range(1, 25))->map(fn ($n) => ['id' => $n]);
        $request = Request::create('/', 'GET', ['per_page' => '10', 'lines_page' => 2]);

        $paginator = ListPaginator::paginateCollection($items, $request, 20, 'lines_page');

        $this->assertCount(10, $paginator->items());
        $this->assertSame(2, $paginator->currentPage());
        $this->assertSame('lines_page', $paginator->getPageName());
    }
}
