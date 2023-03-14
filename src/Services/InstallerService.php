<?php

namespace LaravelReady\Packager\Services;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use LaravelReady\Packager\Exceptions\StrParseException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

use Illuminate\Filesystem\Filesystem;
use LaravelReady\Packager\Supports\StrSupport;
use LaravelReady\Packager\Supports\TemplateSupport;

class InstallerService
{
    private Filesystem $file;

    private TemplateSupport $stubSupport;

    private string $basePath;

    private bool $isThatLaravelApp;

    private array $configs = [
        'PACKAGE_DESC' => 'Laravel Ready Package',
        'PACKAGE_TAGS' => [],
        'SETUP_CONFIG' => true,
        'SETUP_DATABASE' => false,
        'USE_FACADES' => false,
        'SETUP_RESOURCES' => false,
        'SETUP_CONSOLE' => false,
        'SETUP_ROUTES' => false,
        "USE_PHPSTAN" => false,
        'USE_PEST' => false,
        'USE_PHP_CS_FIXER' => false,
        'SETUP_PHPUNIT' => false,
        'SETUP_PACKAGE_DESCRIPTION' => false,
    ];

    public function __construct(array|null $configs = null)
    {
        $this->file = new Filesystem();
        $this->stubSupport = new TemplateSupport();
        $this->basePath = realpath('./') ?: './';

        if ($configs) {
            $this->configs = array_merge($this->configs, $configs);
        }
    }

    public function setBasePath(string|null $subFolder = null): self
    {
        $this->isThatLaravelApp = !empty($subFolder);
        $this->basePath = './';

        if ($subFolder) {
            $path = './' . $subFolder;

            if (!realpath($path)) {
                $this->file->makeDirectory($path);

                $this->basePath = $path;
            } else {
                $this->basePath = $path;
            }
        }

        return $this;
    }

    /**
     * @param string $packageName
     *
     * @return $this
     * @throws StrParseException
     */
    public function setComposerPackageName(string $packageName): self
    {
        $packageName = trim($packageName);
        $_packageName = explode('/', $packageName);

        $this->configs['COMPOSER_PACKAGE_NAME'] = $packageName;
        $this->configs['REPO_URL'] = "https://github.com/{$packageName}";
        $this->configs['PACKAGE_NAMESPACE'] = StrSupport::convertToPascalCase($_packageName[1]);
        $this->configs['PACKAGE_SLUG'] = StrSupport::convertToSlug($_packageName[1]);
        $this->configs['VENDOR_NAMESPACE'] = StrSupport::convertToPascalCase($_packageName[0]);
        $this->configs['FULL_NAMESPACE_JSON'] = "{$this->configs['VENDOR_NAMESPACE']}\\\\{$this->configs['PACKAGE_NAMESPACE']}";
        $this->configs['FULL_NAMESPACE'] = "{$this->configs['VENDOR_NAMESPACE']}\\{$this->configs['PACKAGE_NAMESPACE']}";

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

        return $this;
    }

    public function setPackageDescription(string $description): self
    {
        $this->configs['PACKAGE_DESC'] = $description;

        return $this;
    }

    public function setPackageTags(string $tags): self
    {
        // initial trim
        $tags = trim(trim($tags, ','));

        // splice to array
        $_tags = array_filter(explode(',', $tags), fn ($tag) => !empty($tag));

        // extra trim for each tag
        $_tags = array_map(fn ($_tag) => trim(trim($_tag, ',')), $_tags);

        // order by asc
        usort($_tags, fn ($a, $b) => strlen($a) - strlen($b));

        // remove duplicate tags then filter empty tags
        $_tags = array_filter(array_unique($_tags), fn ($tag) => !empty($tag));

        // reset array keys
        $_tags = array_values($_tags);

        $this->configs['PACKAGE_TAGS'] = $_tags;

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
        $this->configs['USE_PHPSTAN'] = $setup;

        return $this;
    }

    public function setupPest(bool $setup): self
    {
        $this->configs['USE_PEST'] = $setup;

        return $this;
    }

    public function setupPhpCsFixer(bool $setup): self
    {
        $this->configs['USE_PHP_CS_FIXER'] = $setup;

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
     * @throws FileNotFoundException
     */
    public function isThatLaravelApp(): bool
    {
        if ($this->isComposerJsonExists() && $this->file->exists("./artisan")) {
            $composerJsonPath = "./composer.json";

            $composerJson = json_decode($this->file->get($composerJsonPath), true);

            if (isset($composerJson['require']['laravel/framework'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the composer.json file exists.
     *
     * @return bool
     */
    public function isComposerJsonExists(): bool
    {
        return $this->file->exists("./composer.json");
    }

    /**
     * Get current pacakge name.
     *
     * @return string
     */
    public function getCurrentComposerPackage(): ?string
    {
        if ($this->isComposerJsonExists()) {
            $composerJsonPath = "./composer.json";
            $composerJson = json_decode($this->file->get($composerJsonPath), true);

            if (isset($composerJson['name'])) {
                return $composerJson['name'];
            }
        }

        return null;
    }

    /**
     * Initialize the package
     *
     * @return self
     */
    public function init(): self
    {
        if (!$this->file->exists($this->basePath)) {
            mkdir($this->basePath, 0775, true);
        }

        if ($this->isThatLaravelApp) {
            if ($this->file->exists($this->basePath)) {
                $packageFiles = [
                    [
                        'target' => "{$this->basePath}/.gitignore",
                        'stub' => __DIR__ . '/../../resources/stubs/packager/gitignore.blade.php',
                        'allow' => true,
                    ],
                    [
                        'target' => "{$this->basePath}/README.md",
                        'stub' => __DIR__ . '/../../resources/stubs/packager/README.md.blade.php',
                        'allow' => true,
                    ]
                ];

                foreach ($packageFiles as $file) {
                    if ($file['allow']) {
                        $this->stubSupport->replaceTemplate($file['stub'], $file['target']);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Create the package with template files.
     *
     * @return void
     * @throws FileNotFoundException
     * @throws \LaravelReady\Packager\Exceptions\StubException
     */
    public function installPackage(): void
    {
        $packageStubPath = __DIR__ . '/./../../resources/stubs/install/';
        $packageTargetPath = $this->isThatLaravelApp()
            ? "{$this->basePath}/{$this->configs['PACKAGE_SLUG']}"
            : $this->basePath;

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

                // conditional folders
                if (Str::contains($relativePath, 'Con_')) {
                    $pattern = '/Con_[a-zA-Z\d]+/';

                    preg_match(pattern: $pattern, subject: $relativePath, matches: $matches);

                    if (count($matches) === 1) {
                        $data = explode('Con_', $matches[0]);

                        $configName = 'SETUP_' . strtoupper($data[1]);

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
                elseif (str_ends_with($relativePath, '.blade.php') && ($conditionalConfig === null || $conditionalConfig === true)) {
                    $relativePath = Str::replace('.blade.php', '', $relativePath);
                    $fileName = basename($relativePath, ".php");

                    $targetPath = "{$packageTargetPath}/{$relativePath}";

                    // rename the file name to the package name
                    if (Str::contains($fileName, 'PackageName')) {
                        $targetPath = Str::replace('PackageName', $this->configs['PACKAGE_NAMESPACE'], $targetPath);
                    } // rename the file name to the package slug
                    elseif (Str::contains($fileName, 'package-slug')) {
                        $targetPath = Str::replace('package-slug', $this->configs['PACKAGE_SLUG'], $targetPath);
                    }

                    $stubPath = $filePath;

                    $this->stubSupport->replaceTemplate($stubPath, $targetPath, $this->configs);
                }
            }
        }
    }
}
