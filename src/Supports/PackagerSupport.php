<?php

namespace LaravelReady\Packager\Supports;

use Illuminate\Support\Str;

use LaravelReady\Packager\Supports\StringSupport;

class PackagerSupport
{
    /**
     * Parse namespace from given string
     *
     * @param string $makeValue
     * @param string $makeFileType
     * @param string $relativePath
     *
     * @return array
     */
    public static function parseNamespaceFrom(string $makeValue, string $makeFileType, string $relativePath): array
    {
        $commandFolder = '';
        $makeClassName = $makeValue;

        if (Str::contains($makeValue, '\\')) {
            $makeClassName = last(explode('\\', $makeValue));
            $commandFolder = Str::replace("\\{$makeClassName}", '', $makeValue);
        } else if (Str::contains($makeValue, '/')) {
            $makeClassName = last(explode('/', $makeValue));
            $commandFolder = Str::replace("/{$makeClassName}", '', $makeValue);
        }

        if (!Str::endsWith($makeClassName, $makeFileType)) {
            $makeClassName .= $makeFileType;
        }

        $makeClassName = Str::replace('.php', '', $makeClassName);
        $makeClassName = StringSupport::correctClassName($makeClassName);

        // get path in package' src folder
        $makeFolder = "/src/{$relativePath}/{$commandFolder}";

        return [
            'makeClassName' => $makeClassName,
            'makeFolder' => $commandFolder,
            'makeFolderPath' => $makeFolder,
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
        $migrationFiles = array_diff(scandir($path), array('.', '..'));

        return count($migrationFiles);
    }

    /***
     * Validate string is a valid laravel migration file name
     *
     * @return void
     * @throws \Exception
     */
    public static function validateMigrationFileName(string $name): bool{
        return preg_match('/^[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}_[a-z_]+_table$/', $name);
    }
}
