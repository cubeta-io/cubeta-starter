<?php


namespace Cubeta\CubetaStarter;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function files_keys(){
        return [];
    }
}
