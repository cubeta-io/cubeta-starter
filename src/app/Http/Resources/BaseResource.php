<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Collection as DBCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BaseResource extends JsonResource
{
    private const AuthorizedActions = 'authorizedActions';
    private const FilterMethod = 'filterArray';

    protected bool $detailed = false;
    protected ?array $extra;

    public static function collectionWithExtra(DBCollection|LengthAwarePaginator $data, ?array $extra = null, bool $withFilters = false): Collection
    {
        return collect()
            ->wrap($data)
            ->map(fn($item) => self::makeWithExtra($item, $extra))
            ->values()
            ->merge(self::getFilters(get_class($data->first())));
    }

    public static function makeWithExtra(Model $data, array $extra = null): BaseResource
    {
        return self::make($data)->withExtra($extra);
    }

    public function withExtra(?array $extra): static
    {
        $this->extra = $extra;
        return $this;
    }

    /**
     * @param string $class
     * @return Collection
     */
    protected static function getFilters(string $class): Collection
    {
        $filters = collect();

        if (method_exists($class, self::FilterMethod)) {
            $filterCols = collect(call_user_func([$class, self::FilterMethod]));
            $filters = $filters->merge([
                    "filters" => $filterCols->map(fn($item) => [
                        "field" => isset($item["relation"]) ? $item["relation"] . '.' . ($item["field"] ?? $item["name"]) : ($item["field"] ?? $item["name"]),
                        "operator" => $item['operator'] ?? '='
                    ])
                ]
            );
        }
        return $filters;
    }

    /**
     * @template T of Model<T>
     * @param DBCollection|LengthAwarePaginator $data
     * @param array $itemAbilities
     * @param array $generalAbilities
     * @param array|null $extra
     * @param bool $withFilters
     * @return Collection<T>
     */
    public static function collectionWithAbilities(DBCollection|LengthAwarePaginator $data, array $itemAbilities = [], array $generalAbilities = [], ?array $extra = null, bool $withFilters = false): Collection
    {
        $collection = $data->map(function ($item) use ($extra, $itemAbilities, $generalAbilities) {
            $itemAbs = [];
            $class = get_class($item);

            if (count($itemAbilities)) {
                foreach ($itemAbilities as $itemAbility) {
                    $itemAbs["$itemAbility"] = self::getAbilityValue($itemAbility, $class, $item);
                }
            }

            if (!count($itemAbilities) && method_exists($class, self::AuthorizedActions)) {
                foreach (call_user_func([$class, self::AuthorizedActions]) as $action) {
                    if (in_array($action, ["update", "delete", "show"])) {
                        $itemAbs["$action"] = self::getAbilityValue($action, $class, $item);
                    }
                }
            }

            $item->abilities = $itemAbs;

            return $item;
        });

        $genAbs = [];
        $class = get_class($data->first());
        if (count($generalAbilities)) {
            foreach ($generalAbilities as $generalAbility) {
                $genAbs["$generalAbility"] = self::getAbilityValue($generalAbility, $class);
            }
        } else {
            if (method_exists($class, self::AuthorizedActions) && in_array("create", call_user_func([$class, self::AuthorizedActions]))) {
                $genAbs["create"] = self::getAbilityValue("create", $class);
            }
        }

        if (!$withFilters) {
            return collect(["items" => $collection, "abilities" => $genAbs]);
        }

        return collect(["items" => $collection, "abilities" => $genAbs])->merge(self::getFilters($class));
    }

    private static function getAbilityValue(string $ability, string $class, ?Model $modelInstance = null)
    {
        if (auth()->user()) {
            return auth()->user()?->hasPermission($ability, $class, $modelInstance);
        } else if (!method_exists($class, self::AuthorizedActions)) {
            return true;
        } elseif (!in_array($ability, call_user_func([$class, self::AuthorizedActions]))) {
            return true;
        } else
            return false;
    }

    public static function collectionWithDetail(DBCollection|LengthAwarePaginator $data, array $extra = null, bool $withFilters = false): Collection
    {
        return collect()
            ->wrap($data)
            ->map(fn($item) => self::makeWithDetail($item, $extra))
            ->values()
            ->merge(self::getFilters(get_class($data->first())));
    }

    public static function makeWithDetail(Model $data, array $extra = null): BaseResource
    {
        return self::make($data)->withDetail()->withExtra($extra);
    }

    public function withDetail(): static
    {
        $this->detailed = true;
        return $this;
    }

    /**
     * @param Model $data
     * @param array $itemAbilities
     * @param array $generalAbilities
     * @param array|null $extra
     * @return BaseResource|array
     */
    public static function makeWithAbilities(Model $data, array $itemAbilities = [], array $generalAbilities = [], ?array $extra = null): array|BaseResource
    {
        $abilities = [];
        $class = get_class($data);

        if (count($itemAbilities)) {
            foreach ($itemAbilities as $ability) {
                $abilities["$ability"] = self::getAbilityValue($ability, $class, $data);
            }
        }

        if (count($generalAbilities)) {
            foreach ($generalAbilities as $genAbility) {
                $abilities["$genAbility"] = self::getAbilityValue($genAbility, $class, $data);
            }
        }

        if (!count($generalAbilities) && !count($itemAbilities) && method_exists($class, self::AuthorizedActions)) {
            foreach (call_user_func([$class, self::AuthorizedActions]) as $action) {
                if (!in_array($action, ['create', 'index'])) {
                    $abilities["$action"] = self::getAbilityValue($action, $class, $data);
                }
            }
        }

        $response = self::make($data)->withDetail()->withExtra($extra);
        return count($abilities) > 0
            ? array_merge($response->toArray(request()), ["abilities" => $abilities])
            : $response;
    }
}
