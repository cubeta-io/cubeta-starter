<?php

namespace Cubeta\CubetaStarter\Contracts\Policy;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Str;

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
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('index '.$this->modelName)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        if ($user->can('show '.$this->modelName)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->can('store '.$this->modelName)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        if ($user->can('update '.$this->modelName)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        if ($user->can('delete '.$this->modelName)) {
            return true;
        }

        return false;
    }
}
