<?php

namespace Cubeta\CubetaStarter\Enums;

enum ColumnTypeEnum: string
{
    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function isDateTimeType(string $type): bool
    {
        return in_array($type, [
            ColumnTypeEnum::DATE->value,
            ColumnTypeEnum::TIME->value,
            ColumnTypeEnum::DATETIME->value,
            ColumnTypeEnum::TIMESTAMP->value,
        ]);
    }

    public static function isNumericType(string $type): bool
    {
        return in_array($type, [
            ColumnTypeEnum::INTEGER->value,
            ColumnTypeEnum::BIG_INTEGER->value,
            ColumnTypeEnum::UNSIGNED_BIG_INTEGER->value,
            ColumnTypeEnum::DOUBLE->value,
            ColumnTypeEnum::FLOAT->value,
        ]);
    }
    case BIG_INTEGER = 'bigInteger';
    case BOOLEAN = 'boolean';
    case DATE = 'date';
    case DATETIME = 'dateTime';
    case DOUBLE = 'double';
    case FILE = 'file';
    case FLOAT = 'float';
    case INTEGER = 'integer';
    case JSON = 'json';
    case KEY = 'key';
    case STRING = 'string';
    case TEXT = 'text';
    case TIME = 'time';
    case TIMESTAMP = 'timestamp';
    case TRANSLATABLE = 'translatable';
    case UNSIGNED_BIG_INTEGER = 'unsignedBigInteger';
}
