<?php

namespace App\BulkAction;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @template ModelTemplate of Model|QueryBuilder|Builder
 */
class BaseBulkAction
{
    /** @var ModelTemplate */
    protected Builder|QueryBuilder|Model $query;

    protected int $chunkSize = 10;

    protected string $selectableColumn = 'id';

    protected string $actionKey = 'bulk-action';

    protected Request $request;

    /** @var array{array{name:string,method:string,rules:array}} */
    protected array $actions = [
        [
            'name'   => 'delete',
            'method' => 'delete',
            'rules'  => [
                'ids'   => ['array'],
                'ids.*' => ['numeric']
            ]
        ]
    ];

    public function __construct(Builder|QueryBuilder $query, Request $request)
    {
        $this->query = $query;
        $this->request = $request;
    }

    public function run(): bool
    {
        if (!request($this->actionKey)) {
            return false;
        }

        $method = $this->getMethodName();
        return $this->query
            ->when()
            ->whereIn($this->selectableColumn, $this->getSelected())
            ->chunk($this->chunkSize, function (Collection $items) use ($method) {
                if ($method) {
                    $items->each(fn($item) => $this->{$method}($item));
                }
            });
    }

    /**
     * @return string|null
     */
    protected function getMethodName(): ?string
    {
        $action = request($this->actionKey);
        $eqAction = collect($this->actions)
            ->firstWhere(fn($actionItem) => $actionItem['name'] == $action);
        if (!$this->validateRequestData($eqAction['rules'] ?? [])) {
            return null;
        }
        return $eqAction['method'] ?? null;
    }

    protected function getSelected(): array
    {
        return Arr::wrap(request('ids', []));
    }

    /**
     * you may define certain rules that will break the execution this can be done
     * by requests validation rules or be return false for a specific condition
     * in this method
     * @param array $rules
     * @return true
     */
    protected function validateRequestData(array $rules = []): true
    {
        $this->request->validate($rules);
        return true;
    }

    /**
     * this is a demonstration you may define other bulk actions but this assumes
     * that you have a delete bulk action for a given model
     * @param $item
     * @return void
     */
    protected function delete($item): void
    {
        if ($item instanceof Model) {
            $item->delete();
        }
    }
}
