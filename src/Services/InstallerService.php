<?php

namespace LaravelReady\Packager\Services;

use Illuminate\Support\Str;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

use Illuminate\Filesystem\Filesystem;
use LaravelReady\Packager\Supports\StrSupport;
use LaravelReady\Packager\Supports\StubSupport;

class InstallerService
{
    private Filesystem $file;

    private StubSupport $stubSupport;

    private string $basePath;

    private array $configs = [
        'SETUP_CONFIG' => true,
        'SETUP_DATABASE' => false,
        'SETUP_FACADES' => false,
        'SETUP_RESOURCES' => false,
        'SETUP_CONSOLE' => false,
        'SETUP_ROUTES' => false,
        "SETUP_PHPSTAN" => false,
        'SETUP_PEST' => false,
        'SETUP_PHP_CS_FIXER' => false,
        'SETUP_PHPUNIT' => false,
    ];

    public function __construct(array|null $configs = null)
    {
        $this->file = new Filesystem();
        $this->stubSupport = new StubSupport();
        $this->basePath = realpath('./');

        if ($configs) {
            $this->configs = array_merge($this->configs, $configs);

            return $this;
        }
    }

    public function setBasePath(bool $usePackagesFolder = false): self{
        $path = $usePackagesFolder ? './packages' : './';

        $this->basePath = realpath($path);

        return $this;
    }

    /**
     * @param string $packageName
     * @return $this
     */
    public function setComposerPackageName(string $packageName): self
    {
        $packageName = trim($packageName);
        $_packageName = explode('/', $packageName);

        $this->configs['COMPOSER_PACKAGE_NAME'] = $packageName;
        $this->configs['REPO_URL'] = "https://github.com/{$packageName}";
        $this->configs['PACKAGE_NAMESPACE'] = StrSupport::convertToPascalCase($_packageName[1]);
        $this->configs['VENDOR_NAMESPACE'] = StrSupport::convertToPascalCase($_packageName[0]);
        $this->configs['FULL_NAMESPACE_JSON'] = "{$this->configs['PACKAGE_NAMESPACE']}\\\\{$this->configs['VENDOR_NAMESPACE']}";
        $this->configs['FULL_NAMESPACE'] = "{$this->configs['PACKAGE_NAMESPACE']}\\{$this->configs['VENDOR_NAMESPACE']}";

        return $this;
    }

    public function setAuthorName(string $authorName): self
    {
        $this->configs['COMPOSER_AUTHOR_NAME'] = $authorName;

        return $this;
    }

    public function setAuthorEmail(string $authorEmail): self
    {
        $this->configs['COMPOSER_AUTHOR_EMAIL'] = $authorEmail;

        return $this;
    }

    public function setPackageTitle(string $packageTitle): self
    {
        $this->configs['PACKAGE_TITLE'] = $packageTitle;
        $this->configs['PACKAGE_SLUG'] = StrSupport::convertToSlug($packageTitle);

        return $this;
    }

    public function setPackageDescription(string $description): self
    {
        $this->configs['PACKAGE_DESCRIPTION'] = $description;

        return $this;
    }

    public function setPackageTags(string $tags): self
    {
        $tags = trim($tags, ',');

        $_tags = array_filter(explode(',', $tags), fn($tag) => $tag);
        $_tags = array_filter($_tags, fn($tag) => !empty($tag));
        $_tags = array_unique($_tags);
        $_tags = implode('", "', $_tags);

        $this->configs['PACKAGE_TAGS'] = "\"{$_tags}\"";

        return $this;
    }

    public function setupDatabase(bool $setup): self
    {
        $this->configs['SETUP_DATABASE'] = $setup;

        return $this;
    }

    public function setupConfig(bool $setup): self
    {
        $this->configs['SETUP_CONFIG'] = $setup;

        return $this;
    }

    public function setupFacade(bool $setup): self
    {
        $this->configs['SETUP_FACADE'] = $setup;

        return $this;
    }

    public function setupResources(bool $setup): self
    {
        $this->configs['SETUP_RESOURCES'] = $setup;

        return $this;
    }

    public function setupConsole(bool $setup): self
    {
        $this->configs['SETUP_COMMANDS'] = $setup;

        return $this;
    }

    public function setupRoutes(bool $setup): self
    {
        $this->configs['SETUP_ROUTES'] = $setup;

        return $this;
    }

    public function setupPhpStan(bool $setup): self
    {
        $this->configs['SETUP_PHPSTAN'] = $setup;

        return $this;
    }

    public function setupPest(bool $setup): self
    {
        $this->configs['SETUP_PEST'] = $setup;

        return $this;
    }

    public function setupPhpCsFixer(bool $setup): self
    {
        $this->configs['SETUP_PHP_CS_FIXER'] = $setup;

        return $this;
    }

    public function setupPhpUnit(bool $setup): self
    {
        $this->configs['SETUP_PHPUNIT'] = $setup;

        return $this;
    }

    /**
     * @return array
     */
    public function getConfigs(): array
    {
        return $this->configs;
    }

    /**
     * Check if the running laravel app in the root folder.
     *
     * @return bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function isThatLaravelApp(): bool{
        if ($this->file->exists("{$this->basePath}/artisan") && $this->file->exists("{$this->basePath}/composer.json")){
            $composerJsonPath = "{$this->basePath}/composer.json";

            $composerJson = json_decode($this->file->get($composerJsonPath), true);

            if (isset($composerJson['require']['laravel/framework'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check the 'packages' folder if it exists in the root directory.
     *
     * @return bool
     */
    public function init(): self
    {
        if (!$this->file->exists($this->basePath)) {
            mkdir($this->basePath, 0775, true);
        }

        if ($this->file->exists($this->basePath)) {
            $packageFiles = [
                [
                    'target' => "{$this->basePath}/.gitignore",
                    'stub' => __DIR__ . '/../../resources/stubs/packager/gitignore.stub',
                    'allow' => true,
                ],
                [
                    'target' => "{$this->basePath}/README.md",
                    'stub' => __DIR__ . '/../../resources/stubs/packager/README.stub',
                    'allow' => true,
                ]
            ];

            foreach ($packageFiles as $file) {
                if ($file['allow']) {
                    $this->stubSupport->applyStub($file['stub'], $file['target']);
                }
            }
        }

        return $this;
    }

    /**
     * Create the package with template files.
     *
     * @return void
     */
    public function installPackage(): void
    {
        $packageStubPath = __DIR__ . '/./../../resources/stubs/install/';
        $packageTargetPath = realpath('./') . "/packages/{$this->configs['PACKAGE_SLUG']}";

        if (!$this->file->exists($packageTargetPath)) {
            $this->file->makeDirectory($packageTargetPath, 0775, true);
        }

        $filesFolders = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($packageStubPath));

        foreach ($filesFolders as $file) {
            $filePath = $file->getPathname();
            $relativePath = explode('/stubs/install', $filePath);

            if (isset($relativePath[1])) {
                $relativePath = Str::replaceFirst('\\', '', $relativePath[1]);

                $conditionalConfig = null;

                // contional folders
                if (Str::contains($relativePath, 'Con_')) {
                    $pattern = '/Con_[a-zA-Z\d]+/';

                    preg_match(pattern: $pattern, subject: $relativePath, matches: $matches);

                    if (count($matches) === 1) {
                        $data = explode('Con_', $matches[0]);

                        $configName = 'SETUP_'.strtoupper($data[1]);

                        if (isset($this->configs[$configName])) {
                            $conditionalConfig = $this->configs[$configName];

                            $relativePath = Str::replace($matches[0], $data[1], $relativePath);
                        }
                    }
                }

                // first create the folder
                if ($file->isDir() && $relativePath != '.' && !str_ends_with($relativePath, '..')) {
                    $targetPath = "{$packageTargetPath}/{$relativePath}";

                    if (!$this->file->exists($targetPath)) {
                        if ($conditionalConfig === null || $conditionalConfig === true) {
                            $this->file->makeDirectory($targetPath, 0775, true);
                        }
                    }
                } // then copy the file
                else if (str_ends_with($relativePath, '.stub') && ($conditionalConfig === null || $conditionalConfig === true)) {
                    $relativePath = Str::replace('.stub', '', $relativePath);
                    $fileName = basename($relativePath, ".php");

                    $targetPath = "{$packageTargetPath}/{$relativePath}";

                    // rename the file name to the package name
                    if (Str::contains($fileName, 'PackageName')) {
                        $targetPath = Str::replace('PackageName', $this->configs['PACKAGE_NAMESPACE'], $targetPath);
                    } // rename the file name to the package slug
                    else if (Str::contains($fileName, 'package-slug')) {
                        $targetPath = Str::replace('package-slug', $this->configs['PACKAGE_SLUG'], $targetPath);
                    }

                    $stubPath = $filePath;

                    $this->stubSupport->applyStub($stubPath, $targetPath, $this->configs);
                }
            }
        }
    }
}