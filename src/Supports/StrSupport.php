<?php

declare(strict_types=1);

namespace LaravelReady\Packager\Supports;

use LaravelReady\Packager\Exceptions\StrParseException;

use Illuminate\Support\Str;

class StrSupport
{
    /**
     * Correct the string to be used as a namespace or class name
     *
     * @param string $className
     * @param bool   $removeDigits
     *
     * @return string
     * @throws StrParseException
     */
    public static function convertToPascalCase(string $className): string
    {
        $results = preg_replace(pattern: "~^(\d+)~", replacement: '', subject: $className);

        if (empty($className) || empty($results)) {
            throw StrParseException::className();
        }

        $results = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $results)));

        return self::cleanString($results);
    }

    /**
     * Correct the string to be used as a namespace or class name
     *
     * @param string $namespace
     *
     * @return string
     */
    public static function convertToSlug(string $namespace): string
    {
        if (empty($namespace)) {
            throw StrParseException::slug($namespace);
        }

        return Str::slug($namespace);
    }

    /**
     * Clean special characters from string
     *
     * @param string $string
     *
     * @return string
     */
    public static function cleanString(string $string): string
    {
        $result = preg_replace(pattern: '/[^a-zA-Z\d]/', replacement: '', subject: $string);

        if (empty($result)) {
            throw StrParseException::className();
        }

        return $result;
    }

    /**
     * Validate string is a valid composer package name
     *
     * @param string $packageName package name for composer
     *
     * @return bool
     */
    public static function validateComposerPackageName(string $packageName): bool
    {
        $packageName = Str::replace(' ', '', trim($packageName));

        return preg_match(pattern: '/^[a-z-_\d]+\/[a-z-_\d]+$/', subject: $packageName) > 0;
    }

    /**
     * @param string $jsonContent
     *
     * @return string
     */
    public static function jsonFix(string $jsonContent): string
    {
        return preg_replace("/([{,])([a-zA-Z][^: ]+):/", "$1\"$2\":", $jsonContent);
    }
}
