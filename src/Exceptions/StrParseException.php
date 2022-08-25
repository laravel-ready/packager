<?php

namespace LaravelReady\Packager\Exceptions;

use Exception;

final class StrParseException extends Exception
{
    public static function className(): StrParseException
    {
        return new self('Classname cannot be empty');
    }

    public static function slug(string $namespace): StrParseException
    {
        return new self('Slug cannot be empty [' . $namespace . ']');
    }
}
