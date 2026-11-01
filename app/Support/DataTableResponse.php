<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;

class DataTableResponse
{
    public static function make(Request $request, Builder|QueryBuilder $query, array $columns, array $searchable, callable $row, array $translated = [])
    {
        $input = $request->validate([
            'draw' => 'nullable|integer|min:0|max:2147483647',
            'start' => 'nullable|integer|min:0|max:10000000',
            'length' => 'nullable|integer|min:1|max:100',
            'search' => 'nullable|array', 'search.value' => 'nullable|string|max:191',
            'order' => 'nullable|array', 'order.0.column' => 'nullable|integer|min:0|max:100',
            'order.0.dir' => 'nullable|in:asc,desc',
        ]);
        $total = (clone $query)->count();
        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($searchable, $search, $translated) {
                foreach ($searchable as $column) {
                    $builder->orWhere($column, 'like', '%' . $search . '%');
                }
                foreach ($translated as $group) {
                    $matches = array_keys(array_filter($group['labels'], fn ($label) => mb_stripos($label, $search) !== false));
                    if ($matches) {
                        $builder->orWhereIn($group['column'], $matches);
                    }
                }
            });
        }
        $filtered = (clone $query)->count();
        $sort = $columns[(int) $request->input('order.0.column', 0)] ?? $columns[0];
        $query->orderBy($sort, $request->input('order.0.dir') ?: 'desc');
        $rows = $query->skip($input['start'] ?? 0)->take($input['length'] ?? 25)->get();

        return response()->json([
            'draw' => (int) ($input['draw'] ?? 0), 'recordsTotal' => $total,
            'recordsFiltered' => $filtered, 'data' => $rows->map($row)->values(),
        ]);
    }
}
