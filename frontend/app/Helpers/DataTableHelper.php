<?php

namespace App\Helpers;

class DataTableHelper
{
    /**
     * Parse DataTables request and build API filters
     * @param \Illuminate\Http\Request $request
     * @param array $columnMap (DataTables column index => API field)
     * @return array ['filters' => ..., 'draw' => ...]
     */
    public static function parseRequest($request, $columnMap)
    {
        $draw = $request->input('draw');
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value', '');
        $order = $request->input('order.0', []);

        $filters = [
            'page' => ($start / $length) + 1,
            'per_page' => $length,
        ];
        if ($search) {
            $filters['search'] = $search;
        }

        if (!empty($order)) {
            $columnIndex = $order['column'];
            $columnDirection = $order['dir'];
            if (isset($columnMap[$columnIndex])) {
                $filters['sort_by'] = $columnMap[$columnIndex];
                $filters['sort_order'] = $columnDirection;
            }
        }

        return [
            'filters' => $filters,
            'draw' => $draw,
        ];
    }

    /**
     * Format API result for DataTables
     * @param int $draw
     * @param array $apiResult (must have ['success', 'data' => ['data', 'pagination']])
     * @param callable $formatRowCallback (function to format each row)
     * @return \Illuminate\Http\JsonResponse
     */
    public static function formatResponse($draw, $apiResult, $formatRowCallback)
    {
        if (!$apiResult['success']) {
            return response()->json([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $apiResult['message'] ?? 'API error'
            ]);
        }
        $data = $apiResult['data']['data'];
        $pagination = $apiResult['data']['pagination'];
        $formattedData = array_map($formatRowCallback, $data);
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $pagination['total'],
            'recordsFiltered' => $pagination['total'],
            'data' => $formattedData
        ]);
    }
}
