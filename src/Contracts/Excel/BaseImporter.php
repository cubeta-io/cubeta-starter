<?php

namespace App\Excel;

use App\Casts\Translatable;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BaseImporter implements ToModel, WithHeadingRow
{

    private Model $model;

    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    private function mapping()
    {
        return method_exists($this->model, 'importable')
            ? $this->model->importable()
            : $this->model->getFillable();
    }

    /**
     * @inheritDoc
     */
    public function model(array $row): Model|array|null
    {
        if (!method_exists($this->model, 'import')) {
            $import = [];

            foreach ($this->mapping() as $col) {
                $import[$col] = $this->processRow($col, $row[$col]);
            }
            $modelClass = get_class($this->model);
            return new $modelClass($import);
        }
        return $this->model->import($row);
    }

    /**
     * @param string $colName
     * @param $row
     * @return mixed
     */
    private function processRow(string $colName, $row): mixed
    {
        $casts = $this->model->getCasts();
        if (in_array($colName, array_keys($casts)) && $casts[$colName] == Translatable::class && json_encode($row)) {
            return json_encode([
                app()->getLocale() => $row
            ]);
        }

        return $row;
    }
}
