<?php

namespace Cubeta\CubetaStarter\Enums;

enum ColumnTypeEnum: string
{
    case INTEGER = 'integer';
    case BIG_INTEGER = 'bigInteger';
    case UNSIGNED_BIG_INTEGER = 'unsignedBigInteger';
    case DOUBLE = 'double';
    case UNSIGNED_DOUBLE = 'unsignedDouble';
    case FLOAT = 'float';
    case STRING = 'string';
    case TEXT = 'text';
    case JSON = 'json';
    case BOOLEAN = 'boolean';
    case DATE = 'date';
    case TIME = 'time';
    case DATETIME = 'dateTime';
    case TIMESTAMP = 'timestamp';
    case FILE = 'file';
    case KEY = 'key';
    case TRANSLATABLE = 'translatable';

    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
