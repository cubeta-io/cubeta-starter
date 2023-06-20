<?php

namespace Cubeta\CubetaStarter\Contracts\Policy;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Auth\Access\HandlesAuthorization;

class BasePolicy
{
    use HandlesAuthorization;

    protected string $modelName;

    public function __construct()
    {
        $string = static::class;
        $substring1 = 'App\Policies\\';
        $substring2 = 'Policy';
        $this->modelName = Str::lower(str_replace([$substring1, $substring2], '', $string));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return (bool)($user->can('store ' . $this->modelName));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return (bool)($user->can('delete ' . $this->modelName));
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return (bool)($user->can('update ' . $this->modelName));
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return (bool)($user->can('show ' . $this->modelName));
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return (bool)($user->can('index ' . $this->modelName));
    }
}
