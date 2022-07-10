<?php

namespace LaravelReady\Packager\Services;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

use LaravelReady\Packager\Supports\StrSupport;
use LaravelReady\Packager\Supports\StubSupport;
use LaravelReady\Packager\Supports\PackagerSupport;

use Carbon\Carbon;

class PackagerService
{
    public string $basePath;

    private Filesystem $file;

    private StubSupport $stubSupport;

    public function __construct()
    {
        $this->file = new Filesystem();
        $this->stubSupport = new StubSupport();

        $this->basePath = realpath('./');
    }

    /**
     * Create a new migration
     *
     * @param string $tableName
     * @param string $type
     *
     * @return bool|null
     */
    public function makeMigration(string $tableName, string $type): bool|null
    {
        $migrationsPath = "{$this->basePath}/database/migrations";
        $dateTimeSlug = Carbon::now()->format('Y_m_d_');
        $migrationSlug = Str::slug($tableName, '_');

        $migrationSlug = Str::replace(['create_', 'update_', 'delete_', '_table'], '', $migrationSlug);

        if (!$this->file->exists($migrationsPath)) {
            $this->file->makeDirectory($migrationsPath, 0775, true);
        }

        $migrationsCount = PackagerSupport::getMigrationFilesCount($migrationsPath) + 1;
        $dateTimeSlug .= str_repeat('0', (6 - strlen($migrationsCount))) . $migrationsCount;
        $migrationFileName = "{$dateTimeSlug}_{$type}_{$migrationSlug}_table";

        if (!PackagerSupport::validateMigrationFileName($migrationFileName)) {
            throw new \Exception("Invalid migration file name.: {$migrationFileName}");
        }

        $targetPath = "{$migrationsPath}/{$migrationFileName}.php";

        if ($this->file->exists($targetPath)) {
            return null;
        }

        $replacements = [
            'SETUP_SCHEMA_CREATE' => $type === 'create',
            'SCHEMA_TYPE' => $type === 'create' ? 'create' : 'table',
            'TABLE_NAME' => $migrationSlug,
        ];

        $stubPath = __DIR__ . "/../../resources/stubs/command/Migration.php.stub";

        $this->stubSupport->applyStub($stubPath, $targetPath, $replacements);

        return $this->file->exists($targetPath);
    }
}