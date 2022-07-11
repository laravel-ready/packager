<?php

declare(strict_types=1);

namespace LaravelReady\Packager\Supports;

use LaravelReady\Packager\Exceptions\ClassNameException;

use Illuminate\Support\Str;

class StrSupport
{
    /**
     * Correct the string to be used as a namespace or class name
     *
     * @param string $className
     * @param bool $removeDigits
     * @return string
     * @throws ClassNameException
     */
    public static function convertToPascalCase(string $className, bool $removeDigits = true): string
    {
        if (!empty($className)) {
            // is string starting with numeric characters?
            preg_match(pattern: "~^(\d+)~", subject: $className, matches: $numberMatches);

            if (count($numberMatches)) {
                if ($removeDigits) {
                    // remove all numeric characters at the beginning
                    $className = preg_replace(pattern: "~^(\d+)~", replacement: '', subject: $className);
                } else {
                    throw new ClassNameException(message: "Classname cannot start with numeric characters");
                }
            }

            if (empty($className)) return '';

            // remove all non-alphanumeric characters
            $className = preg_replace(pattern: "~[^a-zA-Z\d]~", replacement: ' ', subject: $className);

            if (empty($className)) return '';

            // add a space in front of all capital letters
            $matches = preg_replace(pattern: "([A-Z])", replacement: " $0", subject: $className);

            if (empty($matches)) return '';

            // make capitalize other words then merge them
            $result = implode('', array_map('ucfirst', explode(' ', $matches)));

            if (empty($result)) return '';

            return self::cleanString($result) ?: '';
        }

        return $className;
    }

    /**
     * Correct the string to be used as a namespace or class name
     *
     * @param string $namespace
     * @return string|null
     */
    public static function convertToSlug(string $namespace): ?string
    {
        if (Str::length($namespace) > 0) {
            $result = preg_replace(pattern: "([A-Z])", replacement: " $0", subject: $namespace);

            if (!empty($result)) {
                return Str::slug($result);
            }
        }

        return null;
    }

    /**
     * Clean special characters from string
     *
     * @param string $string
     * @return string|null
     */
    public static function cleanString(string $string): ?string
    {
        $string = str_replace(' ', '', $string);

        $result = preg_replace(pattern: '/[^a-zA-Z\d]/', replacement: '', subject: $string);

        if (!empty($result) && is_string($result)) {
            return $result;
        }

        return null;
    }

    /**
     * Validate string is a valid composer pacakge name
     *
     * @param string $packageName package name for composer
     * @return bool
     */
    public static function validateComposerPackageName(string $packageName): bool
    {
        $packageName = Str::replace(' ', '', trim($packageName));

        return preg_match(pattern: '/^[a-z-_\d]+\/[a-z-_\d]+$/', subject: $packageName) > 0;
    }
}