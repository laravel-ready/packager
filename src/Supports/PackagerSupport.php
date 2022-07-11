<?php

namespace LaravelReady\Packager\Supports;

use Illuminate\Support\Str;

use LaravelReady\Packager\Supports\StrSupport;

class PackagerSupport
{
    /**
     * Parse namespace from given string
     *
     * @param string $makeValue
     * @param string $commandType
     * @param string $relativePath
     *
     * @return array
     * @throws \LaravelReady\Packager\Exceptions\ClassNameException
     */
    public static function parseNamespaceFrom(string $makeValue, string $commandType, string $relativePath): array
    {
        $commandType = ucfirst($commandType);
        $makeValue = Str::replace("{$commandType}.php", '', $makeValue);
        $makeValue = Str::replace('.php', '', $makeValue);

        $commandFolder = '';
        $className = $makeValue;

        if (Str::contains($makeValue, '.')) {
            $makeValue = implode('\\', array_map(function ($part) {
                return StrSupport::convertToPascalCase($part);
            }, explode('.', $makeValue)));
        }

        if (Str::contains($makeValue, '\\')) {
            $className = last(explode('\\', $makeValue));
            $commandFolder = Str::replace("\\{$className}", '', $makeValue);
        }

        $className = StrSupport::convertToPascalCase($className);
        $classNameRaw = $className;

        if ($commandType !== 'model' && !Str::endsWith($className, $commandType)) {
            $className .= $commandType;
        }

        $makeFolder = "/src/{$relativePath}/{$commandFolder}";

        return [
            'classNameRaw' => $classNameRaw,
            'className' => $className,
            'commandFolder' => $commandFolder,
            'commandFolderPath' => $makeFolder,
        ];
    }

    /**
     * Get all migration files count
     *
     * @param string $path
     *
     * @return int
     */
    public static function getMigrationFilesCount(string $path): int
    {
        $dirContents = scandir($path);

        if ($dirContents) {
            $migrationFiles = array_diff($dirContents, ['.', '..']);

            return count($migrationFiles);
        }

        return 0;
    }

    /***
     * Validate string is a valid laravel migration file name
     *
     * @return void
     * @throws \Exception
     */
    public static function validateMigrationFileName(string $name): bool
    {
        return preg_match('/^[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}_[a-z_]+_table$/', $name);
    }
}
