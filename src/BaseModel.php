<?php

namespace Cubeta\CubetaStarter;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return array
     */
    public function filesKeys(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function searchableArray(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function relationsSearchableArray(): array
    {
        return [];
    }
}
