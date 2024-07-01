<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Yajra\DataTables\EloquentDataTable;

trait DataTablesTrait
{
    /**
     * @param Model $item this for send the id or whatever identifier for the routes
     * @param string $routeName this will be suffixed with (.show , .edit , .delete) so just provide base name for the route
     * @param array $exceptActions
     * @param string $additionalActions
     * @return string
     */
    public function crudButtons(Model $item, string $routeName, array $exceptActions = [], string $additionalActions = ""): string
    {
        $buttons = "<div class='d-flex'>";
        if (!in_array("show", $exceptActions)) {
            $buttons .= "<div class='p-1'>
                            <a href='" . route("$routeName.show", $item->id) . "' class='btn btn-xs btn-info'>
                                <i class='bi bi-chevron-bar-right'></i>
                            </a>
                        </div>";
        }
        if (!in_array("edit", $exceptActions)) {
            $buttons .= "<div class='p-1'>
                            <a href='" . route("$routeName.edit", $item->id) . "' class='btn btn-xs btn-success'>
                                <i class='bi bi-pencil-square'></i>
                            </a>
                        </div>";
        }
        if (!in_array("destroy", $exceptActions)) {
            $buttons .= "<div class='p-1'>
                            <button type='button' class='btn btn-xs btn-danger remove-item-from-table-btn'
                                    data-deleteurl ='" . route("$routeName.destroy", $item->id) . "' >
                                <i class='bi bi-trash3-fill'></i>
                            </button>
                        </div>";
        }
        $buttons .= $additionalActions;
        $buttons .= "</div>";
        return $buttons;
    }

    /**
     * @param EloquentDataTable $query
     * @param array{array{orderIndex: int,columnIndex: int,columnName:string}} $columns this is an arrays of arrays the inside array should contain 2 keys : (orderIndex , columnIndex , columnName)
     * @return EloquentDataTable
     */
    public function orderTranslatableColumns($query, array $columns): EloquentDataTable
    {
        $locale = app()->getLocale();

        foreach ($columns as $column) {
            $orderIndex = $column['orderIndex'];
            $columnIndex = $column['columnIndex'];
            $columnName = $column['columnName'];

            if (request()->has('order') && request('order')[$orderIndex]['column'] == $columnIndex) {
                $query->order(function ($query) use ($locale, $orderIndex, $columnName) {
                    if (request('order')[$orderIndex]['dir'] == 'asc') {
                        $query->orderByRaw("JSON_EXTRACT($columnName, ?) ASC", ['$."' . $locale . '"']);
                    } else {
                        $query->orderByRaw("JSON_EXTRACT($columnName, ?) DESC", ['$."' . $locale . '"']);
                    }
                });
            }
        }

        return $query;
    }
}
