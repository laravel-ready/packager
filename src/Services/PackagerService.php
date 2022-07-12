<?php

namespace LaravelReady\Packager\Services;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

use LaravelReady\Packager\Exceptions\StrParseException;
use LaravelReady\Packager\Exceptions\StubException;
use LaravelReady\Packager\Supports\StrSupport;
use LaravelReady\Packager\Supports\StubSupport;
use LaravelReady\Packager\Supports\PackagerSupport;
use LaravelReady\Packager\Supports\Php\PhpSpManipulate;

use Carbon\Carbon;

class PackagerService
{
    public string $basePath;

    private Filesystem $file;

    private StubSupport $stubSupport;

    /**
     * Relative paths for selected commands
     */
    private array $relativePaths = [
        'controller' => 'Http\Controllers',
        'command' => 'Console\Commands',
        'model' => 'Models',
        'request' => 'Http\Requests',
        'service' => 'Services',
        'middleware' => 'Http\Middleware',
    ];

    public function __construct()
    {
        $this->file = new Filesystem();
        $this->stubSupport = new StubSupport();

        // TODO: add support for custom base path
        $this->basePath = realpath('./') ?: './';
    }

    /**
     * Create a new file
     *
     * @param string $makeCommand
     * @param string $makeValue
     *
     * @return bool|null
     * @throws FileNotFoundException
     * @throws StrParseException
     * @throws StubException
     */
    public function make(string $makeCommand, string $makeValue): bool|null
    {
        $replacements = [];
        $composerJsonContent = json_decode($this->file->get("{$this->basePath}/composer.json"), true);
        $composerPackageName = $composerJsonContent['name'] ?? '';

        $namespaces = explode('/', $composerPackageName);
        $replacements['FULL_NAMESPACE'] = StrSupport::convertToPascalCase($namespaces[0]) . '\\' . StrSupport::convertToPascalCase($namespaces[1]);
        $replacements['PACKAGE_SLUG'] = Str::slug($namespaces[1]);

        $parsedNamespace = PackagerSupport::parseNamespaceFrom($makeValue, $makeCommand, $this->relativePaths[$makeCommand]);
        $packageFolderPath = "{$this->basePath}/{$parsedNamespace['commandFolderPath']}";

        if (!$this->file->exists($packageFolderPath)) {
            $this->file->makeDirectory($packageFolderPath, 0775, true);
        }

        $targetPath = "{$this->basePath}/{$parsedNamespace['commandFolderPath']}/{$parsedNamespace['className']}.php";

        if ($this->file->exists($targetPath)) {
            return null;
        }

        $replacements['APPEND_NAMESPACE'] = $parsedNamespace['commandFolder'] ? "\\{$parsedNamespace['commandFolder']}" : '';
        $replacements['MAKE_CLASSNAME'] = $parsedNamespace['className'];
        $replacements['COMMAND_SLUG'] = Str::slug($parsedNamespace['classNameRaw']);

        $stubPath = __DIR__ . "/../../resources/stubs/command/{$makeCommand}.php.stub";

        $this->stubSupport->applyStub($stubPath, $targetPath, $replacements);

        if ($makeCommand === 'command') {
            $serviceProviderPath = "{$this->basePath}/src/ServiceProvider.php";

            if ($this->file->exists($serviceProviderPath)) {
                $phpSpManipulate = new PhpSpManipulate('src/ServiceProvider.php');

                $namespace = "{$replacements['FULL_NAMESPACE']}\\{$this->relativePaths[$makeCommand]}" .
                    ($parsedNamespace['commandFolder'] ? "\\{$parsedNamespace['commandFolder']}" : '') .
                    "\\{$parsedNamespace['className']}";

                // TODO: add register method
                $phpSpManipulate->parse()->appendUse($namespace)->save();
            }
        }

        return $this->file->exists($targetPath);
    }

    /**
     * Create a new migration
     *
     * @param string $tableName
     * @param string $type
     *
     * @return bool|null
     * @throws FileNotFoundException
     * @throws StubException
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