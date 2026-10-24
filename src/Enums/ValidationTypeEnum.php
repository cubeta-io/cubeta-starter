<?php

namespace Cubeta\CubetaStarter\Enums;

/**
 * The way the generated code validates the incoming requests data
 */
enum ValidationTypeEnum: string
{
    /**
     * the generated controllers can depend on one validation class only
     * so when both of them are generated the form request is the used one
     */
    public function controllersUseDto(): bool
    {
        return $this == self::DTO;
    }

    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * does the current type require generating a data transfer object class
     */
    public function hasDto(): bool
    {
        return $this != self::FORM_REQUEST;
    }

    /**
     * does the current type require generating a form request class
     */
    public function hasFormRequest(): bool
    {
        return $this != self::DTO;
    }
    case BOTH = 'Both';
    case DTO = 'DTO';
    case FORM_REQUEST = 'FormRequest';
}
