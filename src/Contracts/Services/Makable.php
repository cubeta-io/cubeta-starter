<?php

namespace Cubeta\CubetaStarter\Contracts\Services;

use App\Services\DataField\DataFieldService;

trait Makable
{
    private static $instance;

    public static function make(): static
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        } elseif (!(self::$instance instanceof static)) {
            self::$instance = new static();
        }
        self::$instance->init();
        return self::$instance;
    }

    public function init(): void
    {
    }
}
