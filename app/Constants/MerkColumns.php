<?php

namespace App\Constants;

class MerkColumns
{
    public const ID             = 'id';
    public const MERK           = 'merk';
    public const IS_ACTIVE      = 'is_active';
    public const CREATED_AT     = 'created_at';
    public const UPDATED_AT     = 'updated_at';

    /**
     * Get fillable columns (exclude id, created_at, updated_at)
     */
    public static function getFillable(): array
    {
        return [
            self::MERK,
            self::IS_ACTIVE,
        ];
    }

    /**
     * Get all columns
     */
    public static function getAll(): array
    {
        return [
            self::ID,
            self::MERK,
            self::IS_ACTIVE,
            self::CREATED_AT,
            self::UPDATED_AT,
        ];
    }

}
