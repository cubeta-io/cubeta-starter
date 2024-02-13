<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Collection as DBCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BaseResource extends JsonResource
{
    protected bool $detailed = false;
    protected ?array $extra;

    public static function collectionWithExtra($data, ?array $extra = null): Collection
    {
        return collect()->wrap($data)->map(fn($item) => self::makeWithExtra($item, $extra))->values();
    }

    public static function makeWithExtra($data, array $extra = null): BaseResource
    {
        return self::make($data)->withExtra($extra);
    }

    public function withExtra($extra): static
    {
        $this->extra = $extra;
        return $this;
    }

    /**
     * @template T of Model<T>
     * @param DBCollection|LengthAwarePaginator $data
     * @param array $itemAbilities
     * @param array $generalAbilities
     * @param array|null $extra
     * @return Collection<T>
     */
    public static function collectionWithAbilities(DBCollection|LengthAwarePaginator $data, array $itemAbilities = [], array $generalAbilities = [], ?array $extra = null): Collection
    {
        if (!in_array(config('cubeta-starter.trait_namespace') . "\HasPermissions", trait_uses_recursive(auth()->user()))) {
            return self::collectionWithDetail($data, $extra);
        }

        $collection = $data->map(function ($item) use ($extra, $itemAbilities, $generalAbilities) {
            $itemAbs = [];
            $class = get_class($item);

            if (count($itemAbilities)) {
                foreach ($itemAbilities as $itemAbility) {
                    $itemAbs["$itemAbility"] = self::getAbilityValue($itemAbility, $class, $item);
                }
            }

            if (!count($itemAbilities) && method_exists($class, 'authorizedActions')) {
                foreach ($class::authorizedActions() as $action) {
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
            if (method_exists($class, 'authorizedActions') && in_array("create", $class::authorizedActions())) {
                $genAbs["create"] = self::getAbilityValue("create", $class);
            }
        }
        return collect(["items" => $collection, "abilities" => $genAbs]);
    }

    public static function collectionWithDetail($data, array $extra = null): Collection
    {
        return collect()->wrap($data)->map(fn($item) => self::makeWithDetail($item, $extra))->values();
    }

    public static function makeWithDetail($data, array $extra = null): BaseResource
    {
        return self::make($data)->withDetail()->withExtra($extra);
    }

    public function withDetail(): static
    {
        $this->detailed = true;
        return $this;
    }

    private static function getAbilityValue(string $ability, string $class, ?Model $modelInstance = null)
    {
        if (auth()->user()) {
            return auth()->user()?->hasPermission($ability, $class, $modelInstance);
        } else if (!method_exists($class, 'authorizedActions')) {
            return true;
        } elseif (!in_array($ability, $class::authorizedActions())) {
            return true;
        } else return false;
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
        if (!in_array(config('cubeta-starter.trait_namespace') . "\HasPermissions", trait_uses_recursive(auth()->user()))) {
            return self::make($data)->withDetail()->withExtra($extra);
        }
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

        if (!count($generalAbilities) && !count($itemAbilities) && method_exists($class, 'authorizedActions')) {
            foreach ($class::authorizedActions() as $action) {
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
