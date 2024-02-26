<?php

namespace Cubeta\CubetaStarter\app\Models\Postman;

/**
 * @template T
 */
interface PostmanObject
{
    /**
     * @return T
     */
    public static function serialize(array $data);

    public function toArray();

    public function collect();

    public function toJson();
}
