<?php

namespace LaravelReady\Packager\Commands;

use Illuminate\Support\Str;
use function Termwind\{render};
use SebastianBergmann\Environment\Console;
use LaravelReady\Packager\Supports\StrSupport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use LaravelReady\Packager\Services\InstallerService;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;
use LaravelReady\Packager\Exceptions\StrParseException;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class NewPackageCommand extends Command
{
    private InstallerService $installerService;
    private bool $autoLoadDetailFromGit = false;
    private SymfonyStyle $io;
    private ProgressBar $progressBar;

    protected $welcomeMessage = "
                           888                                         
                           888                                         
                           888                                         
88888b.   8888b.   .d8888b 888  888  8888b.   .d88b.   .d88b.  888d888 
888  88b      88b d88P     888 .88P      88b d88P 88b d8P  Y8b 888P    
888  888 .d888888 888      888888K  .d888888 888  888 88888888 888     
888 d88P 888  888 Y88b.    888  88b 888  888 Y88b 888 Y8b.     888     
88888P    Y888888   Y8888P 888  888  Y888888   Y88888   Y8888  888     
888                                               888                  
888                                          Y8b d88P                  
888                                            Y88P                    


Welcome to the Packager!
This tool will help you to create a new package for Laravel.
Please, follow the instructions below.\n\n";

    /**
     * The name of the command (the part after "bin/demo").
     *
     * @var string
     */
    protected static $defaultName = 'new arg';

    /**
     * The command description shown when running "php bin/demo list".
     *
     * @var string
     */
    protected static $defaultDescription = 'Iinitialize the package creator';


    protected function configure()
    {
        $this->setDefinition(
            new InputDefinition([
                new InputArgument('vendor-name/package-name', InputArgument::OPTIONAL),
                new InputOption('git', 'g', InputOption::VALUE_OPTIONAL, 'Auto load details from git', 'false'),
            ])
        );
    }

    /**
     * Execute the command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int 0 if everything went fine, or an exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->progressBar = new ProgressBar($output, 50);
        $this->installerService = new InstallerService();

        $this->io->title($this->welcomeMessage);
        $options = $input->getOptions();

        $result = $this->checkCurrentFolder();

        if (!$result) {
            $this->io->error('Operation canceled');

            return Command::FAILURE;
        }

        $this->autoLoadDetailFromGit = ($options['git'] ?? 'false') === 'true';

        // symfony console get command parameters
        // var_dump($input->getArguments(), $options, $input->getFirstArgument());

        $packageNameArg = $input->getArgument('vendor-name/package-name');

        // if has spacific argument
        if ($packageNameArg) {
            $result = $this->checkPackageName($input->getArgument('vendor-name/package-name'));

            if ($result) {
                $this->installerService->setComposerPackageName($packageNameArg);

                $this->runSetup();
            } else {
                $this->askVendorPackageName();
            }
        } else {
            $this->io->info('🔰 Check Point: Meta Details');

            $this->askToSyncGitRepoName();

            $this->runSetup();
        }

        return Command::SUCCESS;
    }

    private function runSetup()
    {
        $this->askToSyncGitUser();
        $this->askMetaDetails();

        $this->io->info('🔰 Check Point: Quick Setup');

        $this->askQuickSetups();

        $this->io->info('🔰 Check Point: Pre Configs');

        $this->askPreConfigs();

        $this->installerService->init()->installPackage();

        $this->io->success(' Your package created successfully. Now, you run "composer install".');
    }

    #region project folder

    /**
     * @throws FileNotFoundException
     */
    private function checkCurrentFolder(): bool
    {
        if ($this->installerService->isComposerJsonExists()) {
            $this->io->warning('You are in a composer project.');
        }

        $this->installerService->setBasePath();

        if ($this->installerService->isThatLaravelApp()) {
            $this->io->write('  ✨ Laravel app found. Packager will create a \"packages\" folder.');

            $answer = $this->io->askQuestion(
                new ConfirmationQuestion('Dou you want to use as monorepo?', false)
            );

            if ($answer) {
                $this->installerService->setBasePath('packages');

                return true;
            }

            return false;
        }

        $currentPackage = $this->installerService->getCurrentComposerPackage();

        if ($currentPackage) {
            $this->io->error("This folder already contains a package: ${currentPackage}");

            return false;
        }

        return true;
    }

    #endregion

    #region [Question Group] Package Name / Sync Git Repo / User Name

    private function askVendorPackageName(): void
    {
        $answer = $this->io->askQuestion(
            new Question('✨ Package name (in "vendor/package" format): ')
        );

        $result = $this->checkPackageName($answer);

        if ($result) {
            $this->installerService->setComposerPackageName($answer);

            return;
        }

        $this->askVendorPackageName();
    }

    private function askToSyncGitRepoName(): void
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
                    $this->io->info('✨ Git repo found.');

                    if ($this->autoLoadDetailFromGit) {
                        $this->installerService->setComposerPackageName($composerPackageName);

                        $this->io->info("[Autofilled] Repo Name: {$composerPackageName}");

                        return;
                    } else {
                        $answer = $this->io->askQuestion(
                            new ConfirmationQuestion("Do you want to use this repo name ({$composerPackageName})?", false)
                        );

                        if (!$answer) {
                            $this->askVendorPackageName();

                            return;
                        }

                        $this->io->info("Repo Name: {$composerPackageName}");

                        $this->installerService->setComposerPackageName($composerPackageName);
                    }
                }
            }
        }

        $this->askVendorPackageName();
    }

    private function askToSyncGitUser(): void
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

            $this->io->info('✨ Git user found.');

            if ($this->autoLoadDetailFromGit) {
                $this->installerService->setAuthorName($gitUserName);
                $this->installerService->setAuthorEmail($gitUserEmail);

                $this->io->info("[Autofilled] Username: {$gitUserName}, Email: {$gitUserEmail}");

                return;
            } else {
                $answer = $this->io->askQuestion(
                    new ConfirmationQuestion('Do you want to use this git user?', true)
                );

                if (!$answer) {
                    $this->askAuthorName();
                    $this->askAuthorEmail();
                }

                $this->io->info("Username: {$gitUserName}, Email: {$gitUserEmail}");

                $this->installerService->setAuthorName($gitUserName);
                $this->installerService->setAuthorEmail($gitUserEmail);

                return;
            }
        }

        $this->askAuthorName();
        $this->askAuthorEmail();
    }

    #endregion

    #region [Question Group] User Name / Email

    private function askAuthorName(): void
    {
        $answer = $this->io->askQuestion(
            new Question('✨ Author Name: ')
        );

        if (empty($answer)) {
            $this->askAuthorName();

            return;
        }

        $answer = StrSupport::cleanString($answer);

        $this->installerService->setAuthorName($answer);
    }

    private function askAuthorEmail(): void
    {
        $answer = $this->io->askQuestion(
            new Question('✨ Author Email: ')
        );

        if (empty($answer)) {
            $this->askAuthorEmail();

            return;
        } elseif (filter_var($answer, FILTER_VALIDATE_EMAIL)) {
            $this->io->info("⚠  Invalid email address: {$answer}");

            $this->askAuthorEmail();

            return;
        } else {
            $answer = StrSupport::cleanString($answer);

            $this->installerService->setAuthorEmail($answer);
        }
    }

    #endregion

    #region [Question Group] Meta Info

    /**
     * @return void
     * @throws StrParseException
     */
    private function askMetaDetails(): void
    {
        $this->askPackageTitle();
        $this->askPackageDescription();
        $this->askPackageTags();
    }

    /**
     * @return void
     * @throws StrParseException
     */
    private function askPackageTitle(): void
    {
        $composerPackageName = $this->installerService->getConfigs()['COMPOSER_PACKAGE_NAME'] ?? null;
        $packageTitle = '';

        if ($composerPackageName) {
            $packageTitle = explode('/', $composerPackageName)[1] ?? null;
            $packageTitle = $packageTitle ? StrSupport::convertToPascalCase($packageTitle) : null;
        }

        $packageTitle = $packageTitle ?: '';
        $answer = $this->io->askQuestion(
            new Question('✨ Package Title: ', $packageTitle)
        );

        $answer = trim($answer ?? '');

        if (empty($answer)) {
            $this->askPackageTitle();

            return;
        }

        $this->io->info("Chosen: {$packageTitle} ");
        $this->installerService->setPackageTitle($packageTitle);
    }

    /**
     * @return void
     */
    private function askPackageDescription(): void
    {
        $answer = $this->io->askQuestion(
            new Question('✨ Package Description [optional]: ')
        );

        $answer = trim($answer ?? '');

        if (!empty($answer)) {
            $description = ucfirst(trim($answer));
            $this->installerService->setPackageDescription($description);

            $this->io->info("Chosen: {$description} ");
        }
    }

    /**
     * @return void
     */
    private function askPackageTags(): void
    {
        $answer = $this->io->askQuestion(
            new Question('✨ Package Tags (split with comma) [optional]: ')
        );

        $answer = trim($answer ?? '');

        if (!empty($answer)) {
            $tags = trim($answer);
            $this->installerService->setPackageTags($tags);

            $tags = $this->installerService->getConfigs()['PACKAGE_TAGS'] ?? null;

            $this->io->info("Chosen: {$tags} ");
        }
    }

    #endregion

    #region [Question Group] Quick Setup

    /**
     * @return void
     */
    private function askQuickSetups(): void
    {
        $answer = $this->io->askQuestion(
            new ConfirmationQuestion('✨ Do you want to apply quick setups?' . PHP_EOL . '  This step contains config, database, facade, resources, console, and route setups.' . PHP_EOL, true, '/^(y|all|j)/i')
        );

        if ($answer) {
            $this->askConfigSetup();
            $this->askDatabaseSetup();
            $this->askFacadeSetup();
            $this->askResourcesSetup();
            $this->askConsoleSetup();
            $this->askRoutesSetup();
        }
    }

    /**
     * @return void
     */
    private function askConfigSetup(): void
    {
        $answer = $this->io->askQuestion(
            new ConfirmationQuestion('🟠 Add config?')
        );

        $this->installerService->setupConfig($answer);
    }

    /**
     * @return void
     */
    private function askDatabaseSetup(): void
    {
        $answer = $this->io->askQuestion(
            new ConfirmationQuestion('🟠 Add database?')
        );

        $this->installerService->setupDatabase($answer);
    }

    /**
     * @return void
     */
    private function askFacadeSetup(): void
    {
        $answer = $this->io->askQuestion(
            new ConfirmationQuestion('🟠 Add facade?')
        );

        $this->installerService->setupFacade($answer);
    }

    /**
     * @return void
     */
    private function askResourcesSetup(): void
    {
        $answer = $this->io->askQuestion(
            new ConfirmationQuestion('🟠 Add resources?')
        );

        $this->installerService->setupResources($answer);
    }

    /**
     * @return void
     */
    private function askConsoleSetup(): void
    {
        $answer = $this->io->askQuestion(
            new ConfirmationQuestion('🟠 Add commands?')
        );

        $this->installerService->setupConsole($answer);
    }

    /**
     * @return void
     */
    private function askRoutesSetup(): void
    {
        $answer = $this->io->askQuestion(
            new ConfirmationQuestion('🟠 Add routes?')
        );

        $this->installerService->setupRoutes($answer);
    }

    // #endregion

    // #region pre-configs

    // private function askPreConfigs(): void
    // {
    //     $this->io->info('✨ Do you want to apply pre-configs? (yes/no)');
    //     $this->io->out("This step contains ...", 'italic');
    //     
    //     

    //     
    //     $answer = $input->read();

    //     if ($answer) {
    //         $this->askPhpStan();
    //         $this->askPest();
    //         $this->askPhpCsFixer();
    //     }
    // }

    // /**
    //  * @return void
    //  */
    // private function askPhpStan(): void
    // {
    //     $this->io->info("🟠 Add PHPStan for linting?");

    //     
    //     $answer = $input->read();

    //     $this->installerService->setupPhpStan($answer);
    // }

    // /**
    //  * @return void
    //  */
    // private function askPest(): void
    // {
    //     $this->io->info("🟠 Add Pest for testing?");

    //     
    //     $answer = $input->read();

    //     $this->installerService->setupPest($answer);
    // }

    // /**
    //  * @return void
    //  */
    // private function askPhpCsFixer(): void
    // {
    //     $this->io->info("🟠 Add PHP-CS-Fixer for fixing coding standards issues?");

    //     
    //     $answer = $input->read();

    //     $this->installerService->setupPhpCsFixer($answer);
    //     $this->installerService->setupPhpUnit($answer);
    // }

    #endregion

    #region [Question Group] Pre-configs

    private function askPreConfigs(): void
    {
        $answer = $this->io->askQuestion(
            new ConfirmationQuestion('✨ Do you want to apply pre-configs?')
        );

        if ($answer) {
            $this->askPhpStan();
            $this->askPest();
            $this->askPhpCsFixer();
        }
    }

    /**
     * @return void
     */
    private function askPhpStan(): void
    {
        $answer = $this->io->askQuestion(
            new ConfirmationQuestion('🟠 Add PHPStan for linting?')
        );

        $this->installerService->setupPhpStan($answer);
    }

    /**
     * @return void
     */
    private function askPest(): void
    {
        $answer = $this->io->askQuestion(
            new ConfirmationQuestion('🟠 Add Pest for testing?')
        );

        $this->installerService->setupPest($answer);
    }

    /**
     * @return void
     */
    private function askPhpCsFixer(): void
    {
        $answer = $this->io->askQuestion(
            new ConfirmationQuestion('🟠 Add PHP-CS-Fixer for fixing coding standards issues?')
        );

        $this->installerService->setupPhpCsFixer($answer);
        $this->installerService->setupPhpUnit($answer);
    }

    #endregion

    #region Helpers

    private function checkPackageName(string $answer): bool
    {
        if (empty($answer)) {
            return false;
        }

        if (!StrSupport::validateComposerPackageName($answer)) {
            $this->io->write("Your input: \"{$answer}\" ");
            $this->io->error('⚠  Package name must be in "vendor-name/package-name" format. Invalid package name. Please, try again.');

            render(<<<'HTML'
                <div class="bg-red-400 text-white text-gray-50">
                    
                </div>
            HTML);


            return false;
        }

        return true;
    }

    #endregion
}
