<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;

class WebController extends Controller
{
    public array $relations = [];

    public function paginationData(LengthAwarePaginator $data): array
    {
        return [
            'current_page' => $data->currentPage(),
            'from' => $data->firstItem(),
            'to' => $data->lastItem(),
            'total' => $data->total(),
            'per_page' => $data->perPage(),
            'total_pages' => $data->lastPage(),
            'is_first_page' => $data->onFirstPage(),
            'is_last_page' => $data->onLastPage(),
        ];
    }
}
