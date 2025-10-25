<?php 
namespace App\Traits;

trait PaginateTrait
{
    public function paginateQuery($query, $request)
    {
        $perPage = $request->per_page ?? 15;
        $page = $request->page ?? 1;

        $total = $query->count();
        $results = $query->forPage($page, $perPage)->get();

        return [
            'data' => $results,
            'meta' => [
                'per_page' => (int) $perPage,
                'current_page' => (int) $page,
                'last_page' => (int) ceil($total / $perPage),
                'total' => (int) $total,
            ],
        ];
    }
}
