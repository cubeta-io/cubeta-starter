<?php

namespace Cubeta\CubetaStarter\Enums;

enum ColumnTypeEnum: string
{
    case INTEGER = 'integer';
    case BIG_INTEGER = 'bigInteger';
    case UNSIGNED_BIG_INTEGER = 'unsignedBigInteger';
    case DOUBLE = 'double';
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

    public static function isNumericType(string $type): bool
    {
        return in_array($type, [
            ColumnTypeEnum::INTEGER->value,
            ColumnTypeEnum::BIG_INTEGER->value,
            ColumnTypeEnum::UNSIGNED_BIG_INTEGER->value,
            ColumnTypeEnum::DOUBLE->value,
            ColumnTypeEnum::FLOAT->value
        ]);
    }

    public static function isDateTimeType(string $type): bool
    {
        return in_array($type, [
            ColumnTypeEnum::DATE->value,
            ColumnTypeEnum::TIME->value,
            ColumnTypeEnum::DATETIME->value,
            ColumnTypeEnum::TIMESTAMP->value
        ]);
    }
}
