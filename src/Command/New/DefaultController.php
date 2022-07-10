<?php

namespace LaravelReady\Packager\Command\New;

use Illuminate\Support\Str;
use LaravelReady\Packager\Services\InstallerService;
use LaravelReady\Packager\Supports\StrSupport;

use Minicli\Input;
use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    private InstallerService $installerService;
    private $autoGitMetaLoad = false;

    public function handle(): void
    {
        $this->installerService = new InstallerService();

        if ($this->hasParam('git')) {
            $this->autoGitMetaLoad = $paramGit = $this->getParam('git') === 'true';
        }

        $this->getPrinter()->info("
     ____            _
    |  _ \ __ _  ___| | ____ _  __ _  ___ _ __
    | |_) / _` |/ __| |/ / _` |/ _` |/ _ \ '__|
    |  __/ (_| | (__|   < (_| | (_| |  __/ |
    |_|   \__,_|\___|_|\_\__,_|\__, |\___|_|
                               |___/
        ");

        $result = $this->checkCurrentFolder();

        if ($result) {
            $this->askToSyncGitRepoName();
            $this->askToSyncGitUser();
            $this->askMetaDetails();

            $this->getPrinter()->success('🔰 Check Point: Quick Setup');
            $this->getPrinter()->newline();

            $this->askQuickSetups();
            $this->askPreconfigs();

            $this->installerService->init()->installPackage();

            $this->getPrinter()->success(' Your package installed successfully ', true);
            $this->getPrinter()->newline();
        } else {
            $this->getPrinter()->error(' Operation canceled ', true);
            $this->getPrinter()->newline();
        }
    }

    private function isItSaidYes(string $userInput): bool
    {
        return $userInput == 'yes' || $userInput == 'ye' || $userInput == 'y';
    }

    #region project folder

    private function checkCurrentFolder(): bool
    {
        if ($this->installerService->isThatLaravelApp()) {
            $this->getPrinter()->info('✨ Laravel app found. Dou you want to use monorepo? (yes/no)');
            $this->getPrinter()->out("Packager will create a \"packages\" folder.", 'italic');
            $this->getPrinter()->newline();
            $this->getPrinter()->newline();

            $input = new Input();
            $userInput = $input->read();

            if ($this->isItSaidYes($userInput)) {
                $this->installerService->setBasePath(true);
            } else {
                return false;
            }
        }

        $this->installerService->setBasePath(false);

        return true;
    }

    #emdregion

    #region git repo

    private function askToSyncGitRepoName()
    {
        $gitRepoAddress = null;

        try {
            $gitRepoAddress = shell_exec('git config remote.origin.url');
        } catch (\Exception $e) {
        }

        if (!empty($gitRepoAddress)) {
            $gitRepoAddress = trim($gitRepoAddress);

            $composerPackageName = shell_exec('git config remote.origin.url');

            if ($composerPackageName) {
                $composerPackageName = Str::replaceFirst('https://github.com/', '', $composerPackageName);
                $composerPackageName = Str::replaceLast('.git', '', $composerPackageName);

                if (StrSupport::validateComposerPackageName($composerPackageName)) {
                    $this->getPrinter()->info('✨ Git repo found. Do you want to use this repo name? (yes/no)');

                    if ($this->autoGitMetaLoad) {
                        $this->installerService->setComposerPackageName($composerPackageName);

                        $this->getPrinter()->out("[Autofilled] Repo Name: {$composerPackageName}", 'italic');
                        $this->getPrinter()->newline();
                        $this->getPrinter()->newline();
                    } else {
                        $this->getPrinter()->newline();
                        $this->getPrinter()->out("Repo Name: {$composerPackageName}", 'italic');
                        $this->getPrinter()->newline();
                        $this->getPrinter()->newline();

                        $input = new Input();
                        $userInput = $input->read();

                        if ($this->isItSaidYes($userInput)) {
                            $this->installerService->setComposerPackageName($composerPackageName);

                            return;
                        }
                    }
                }
            }
        }

        $this->askVendorName();
    }

    private function askVendorName()
    {
        $this->getPrinter()->info('✨ Package name (in "vendor/package" format): ');

        $input = new Input();
        $userInput = $input->read();

        if (empty($userInput)) {
            $this->askVendorName();
        }

        if (!StrSupport::validateComposerPackageName($userInput)) {
            $this->getPrinter()->display(">>>> {$userInput} ", true);

            $this->getPrinter()->error('⚠  Package name must be in "vendor/package" format.');

            $this->askVendorName();
        }

        $this->installerService->setComposerPackageName($userInput);
    }

    #emdregion

    #region git user

    private function askToSyncGitUser()
    {
        $gitUserName = null;
        $gitUserEmail = null;

        try {
            $gitUserName = shell_exec('git config user.name');
            $gitUserEmail = shell_exec('git config user.email');
        } catch (\Exception $e) {
        }

        if (!empty($gitUserName) && !empty($gitUserEmail)) {
            $gitUserName = trim($gitUserName);
            $gitUserEmail = trim($gitUserEmail);

            $this->getPrinter()->info('✨ Git user found. Do you want to use this user name and email address? (yes/no)');

            if ($this->autoGitMetaLoad) {
                $this->installerService->setAuthorName($gitUserName);
                $this->installerService->setAuthorEmail($gitUserEmail);

                $this->getPrinter()->out("[Autofilled] Username: {$gitUserName}", 'italic');
                $this->getPrinter()->newline();
                $this->getPrinter()->out("[Autofilled] Email: {$gitUserEmail}", 'italic');
                $this->getPrinter()->newline();
                $this->getPrinter()->newline();

                return;
            } else {
                $this->getPrinter()->newline();
                $this->getPrinter()->out("Username: {$gitUserName}", 'italic');
                $this->getPrinter()->newline();
                $this->getPrinter()->out("Email: {$gitUserEmail}", 'italic');
                $this->getPrinter()->newline();
                $this->getPrinter()->newline();

                $input = new Input();
                $userInput = $input->read();

                if ($this->isItSaidYes($userInput)) {
                    $this->installerService->setAuthorName($gitUserName);
                    $this->installerService->setAuthorEmail($gitUserEmail);

                    return;
                }
            }
        }

        $this->askAuthorName();
        $this->askAuthorEmail();
    }

    private function askAuthorName()
    {
        $this->getPrinter()->info('✨ Author Name: ');

        $input = new Input();
        $userInput = $input->read();

        if (empty($userInput)) {
            $this->askAuthorName();
        }

        $userInput = StrSupport::cleanString($userInput);

        $this->installerService->setAuthorName($userInput);
    }

    private function askAuthorEmail()
    {
        $this->getPrinter()->info('✨ Author Email: ');

        $input = new Input();
        $userInput = $input->read();

        if (empty($userInput)) {
            $this->askAuthorEmail();
        } else if (filter_var($userInput, FILTER_VALIDATE_EMAIL)) {
            $this->getPrinter()->display(">>>> {$userInput} ", true);

            $this->getPrinter()->error('⚠  Invalid email address!');

            $this->askAuthorEmail();
        }

        $userInput = StrSupport::cleanString($userInput);

        $this->installerService->setAuthorEmail($userInput);
    }

    #emdregion


    #region meta info

    private function askMetaDetails()
    {
        $this->askPackageTitle();
        $this->askPackageDescription();
        $this->askPackageTags();
    }

    private function askPackageTitle()
    {
        $composerPackageName = $this->installerService->getConfigs()['COMPOSER_PACKAGE_NAME'] ?? null;
        $packageTitle = '';

        if ($composerPackageName) {
            $packageTitle = explode('/', $composerPackageName)[1] ?? null;
            $packageTitle = $packageTitle ? StrSupport::convertToPascalCase($packageTitle) : null;
        }

        $packageTitle = $packageTitle ?: '';
        $this->getPrinter()->info("✨ Package Title ({$packageTitle}) [optional]: ");

        if ($this->autoGitMetaLoad) {
            $this->getPrinter()->display(">>>> [Autofilled] Chosen: {$packageTitle} ");

            $this->installerService->setPackageTitle($packageTitle);
        } else {
            $input = new Input();
            $userInput = $input->read();

            if (!empty($userInput)) {
                $packageTitle = trim($userInput);
            }

            $this->getPrinter()->display(">>>> Chosen: {$packageTitle} ");

            $this->installerService->setPackageTitle($packageTitle);
        }
    }

    private function askPackageDescription()
    {
        $this->getPrinter()->info('✨ Package Description [optional]: ');

        $input = new Input();
        $userInput = $input->read();

        if (!empty($userInput)) {
            $description = ucfirst(trim($userInput));
            $this->installerService->setPackageDescription($description);

            $this->getPrinter()->display(">>>> Chosen: {$description} ");
            $this->getPrinter()->newline();
        }
    }

    private function askPackageTags()
    {
        $this->getPrinter()->info('✨ Package Tags (split with comma) [optional]: ');

        $input = new Input();
        $userInput = $input->read();

        if (!empty($userInput)) {
            $tags = trim($userInput);
            $this->installerService->setPackageTags($tags);

            $tags = $this->installerService->getConfigs()['PACKAGE_TAGS'] ?? null;

            $this->getPrinter()->display(">>>> Chosen: {$tags} ");
            $this->getPrinter()->newline();
        }
    }

    #emdregion

    #region quick setup

    private function askQuickSetups()
    {
        $this->getPrinter()->info('✨ Do you want to apply quick setups? (yes/no)');
        $this->getPrinter()->out("This step contains config, database, facade, resources, console, and route setups.", 'italic');
        $this->getPrinter()->newline();
        $this->getPrinter()->newline();

        $input = new Input();
        $userInput = $input->read();

        if ($this->isItSaidYes($userInput)) {
            $this->askConfigSetup();
            $this->askDatabaseSetup();
            $this->askFacadeSetup();
            $this->askResourcesSetup();
            $this->askConsoleSetup();
            $this->askRoutesSetup();
        }
    }

    private function askConfigSetup()
    {
        $this->getPrinter()->info("📍 Add config? (yes/no)");

        $input = new Input();
        $userInput = $input->read();

        $this->installerService->setupConfig($this->isItSaidYes($userInput));
    }

    private function askDatabaseSetup()
    {
        $this->getPrinter()->info("📍 Add database? (yes/no)");

        $input = new Input();
        $userInput = $input->read();

        $this->installerService->setupDatabase($this->isItSaidYes($userInput));
    }

    private function askFacadeSetup()
    {
        $this->getPrinter()->info("📍 Add facade? (yes/no)");

        $input = new Input();
        $userInput = $input->read();

        $this->installerService->setupFacade($this->isItSaidYes($userInput));
    }

    private function askResourcesSetup()
    {
        $this->getPrinter()->info("📍 Add resources? (yes/no)");

        $input = new Input();
        $userInput = $input->read();

        $this->installerService->setupResources($this->isItSaidYes($userInput));
    }

    private function askConsoleSetup()
    {
        $this->getPrinter()->info("📍 Add commands? (yes/no)");

        $input = new Input();
        $userInput = $input->read();

        $this->installerService->setupConsole($this->isItSaidYes($userInput));
    }

    private function askRoutesSetup()
    {
        $this->getPrinter()->info("📍 Add routes? (yes/no)");

        $input = new Input();
        $userInput = $input->read();

        $this->installerService->setupRoutes($this->isItSaidYes($userInput));
    }

    #emdregion

    #region pre-configs

    private function askPreconfigs()
    {
        $this->getPrinter()->info('✨ Do you want to apply pre-configs? (yes/no)');
        $this->getPrinter()->out("This step contains ...", 'italic');
        $this->getPrinter()->newline();
        $this->getPrinter()->newline();

        $input = new Input();
        $userInput = $input->read();

        if ($this->isItSaidYes($userInput)) {
            $this->askPhpStan();
            $this->askPest();
            $this->askPhpCsFixer();
        }
    }

    private function askPhpStan()
    {
        $this->getPrinter()->info("📍 Add PHPStan for linting? (yes/no)");

        $input = new Input();
        $userInput = $input->read();

        $this->installerService->setupPhpStan($this->isItSaidYes($userInput));
    }

    private function askPest()
    {
        $this->getPrinter()->info("📍 Add Pest for testing? (yes/no)");

        $input = new Input();
        $userInput = $input->read();

        $this->installerService->setupPest($this->isItSaidYes($userInput));
    }

    private function askPhpCsFixer()
    {
        $this->getPrinter()->info("📍 Add PHP-CS-Fixer for fixing coding standarts issues? (yes/no)");

        $input = new Input();
        $userInput = $input->read();

        $this->installerService->setupPhpCsFixer($this->isItSaidYes($userInput));
        $this->installerService->setupPhpUnit($this->isItSaidYes($userInput));
    }

    #emdregion
}