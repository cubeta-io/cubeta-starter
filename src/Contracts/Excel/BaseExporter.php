<?php

namespace App\Excel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BaseExporter implements FromCollection, WithMapping, WithHeadings
{
    public array|Collection|null $collection = null;

    public ?Model $model = null;

    public ?array $requestCols = null;

    public function __construct(Collection|array $collection, Model $model, ?array $requestCols)
    {
        $this->collection = $collection;
        $this->model = $model;
        $this->requestCols = $requestCols;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function map($row): array
    {
        $map = [];

        if (method_exists($this->model, 'exportable')) {
            $columns = $this->model->exportable();
        } else $columns = $this->model->getFillable();

        foreach ($columns as $col) {

            if ($this->requestCols && !in_array($col, $this->requestCols)) {
                continue;
            }

            if (Str::contains($col, ".")) {
                $relation = explode(".", $col);
                $val = $row;

                for ($i = 0; $i < count($relation); $i++) {
                    $val = $val->{"{$relation[$i]}"};
                }

                $map[] = $val;
            } else {
                $map[] = $row->{"{$col}"};
            }
        }

        return $map;
    }

    public function headings(): array
    {
        if (method_exists($this->model, 'exportable')) {
            $heads = $this->model->exportable();
        } else $heads = $this->model->getFillable();

        if ($this->requestCols) {
            $heads = collect($this->requestCols)->intersect($heads)->toArray();
        }

        foreach ($heads as $key => $head) {
            $heads[$key] = Str::title(Str::replace(['.', '-', '_'], ' ', $head));
        }

        return $heads;
    }
}
