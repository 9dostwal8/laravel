<?php

namespace App\Helpers;

class SaveTemporaryFields
{
    private static array $fields = [];

    public static function setFields($fields)
    {
        static::$fields = $fields;
    }

    public static function getFields()
    {
        return static::$fields;
    }
}
