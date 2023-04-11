<?php

namespace Cubeta\CubetaStarter\Contracts\Policy;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Str;

class BasePolicy
{
    use HandlesAuthorization;

    protected $modelName;

    public function __construct()
    {
        $string = static::class;
        $substring1 = 'App\Policies\\';
        $substring2 = 'Policy';
        $this->modelName = Str::lower(str_replace([$substring1, $substring2], '', $string));
    }

    /**
     * Determine whether the user can view any models.
     *
     * @return Response|bool
     */
    public function viewAny(User $user)
    {
        if ($user->can('index '.$this->modelName)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return Response|bool
     */
    public function view(User $user)
    {
        if ($user->can('show '.$this->modelName)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @return Response|bool
     */
    public function create(User $user)
    {
        if ($user->can('store '.$this->modelName)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return Response|bool
     */
    public function update(User $user)
    {
        if ($user->can('update '.$this->modelName)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return Response|bool
     */
    public function delete(User $user)
    {
        if ($user->can('delete '.$this->modelName)) {
            return true;
        }

        return false;
    }
}
