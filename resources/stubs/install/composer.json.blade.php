{
    "name": "{{ $COMPOSER_PACKAGE_NAME }}",
    "description": "{{ $PACKAGE_DESC }}",
    "type": "library",
    "license": "MIT",
    "version": "1.0.0",
@if ($PACKAGE_TAGS && count($PACKAGE_TAGS))    "keywords": [
@foreach ($PACKAGE_TAGS as $key => $tag)
    "{{ $tag }}"@if ($key !== count($PACKAGE_TAGS) - 1),@endif

    @endforeach
    ],@endif
    @if($COMPOSER_AUTHOR_NAME && $COMPOSER_AUTHOR_EMAIL)"authors": [
        {
            "name": "{{ $COMPOSER_AUTHOR_NAME }}",
            "email": "{{ $COMPOSER_AUTHOR_EMAIL }}"
        }
    ],@endif
    "support": {
        "issues": "{{ $REPO_URL }}/issues",
        "source": "{{ $REPO_URL }}"
    },
    "require": {
        "php": "^8.1",
        "illuminate/support": "^10.3"
    },
    "require-dev": {
        "mockery/mockery": "^1.5",
        "orchestra/testbench": "^8.0"@if ($USE_PHPSTAN),
        "phpstan/phpstan": "^1.10",
        "phpstan/phpstan-phpunit": "^1.3",
        "phpstan/phpstan-deprecation-rules": "^1.1",
        "phpstan/extension-installer": "^1.2",
        "nunomaduro/larastan": "^2.5"@endif @if ($USE_PEST),
        "pestphp/pest": "^1.22",
        "pestphp/pest-plugin-laravel": "^1.4",
        "pestphp/pest-plugin-parallel": "^1.2"@endif
    },
    "autoload": {
        "psr-4": {
            "{{ $FULL_NAMESPACE_JSON }}\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests"
        }
    },
    "config": {
        "preferred-install": "dist",
        "sort-packages": true
    },
    "scripts":{ @if($USE_PHP_CS_FIXER)
        "lint": "php-cs-fixer fix -v"@endif @if($USE_PEST),
        "test:coverage": "@test --coverage-php ./coverage/cov/default.cov",
        "test:coverage:html": "@test --coverage-html coverage/html/default",
        "test": "vendor/bin/pest --colors=always --parallel",@endif @if($USE_PHP_CS_FIXER)
        "test:lint": "php-cs-fixer fix -v --dry-run",@endif @if($USE_PHPSTAN)
        "test:styles": "vendor/bin/phpstan analyse --ansi",
        "test:styles:pro": "vendor/bin/phpstan analyse --pro --fix --watch"@endif
    },
    "extra": {
        "laravel": {
            "providers": [
                "{{ $FULL_NAMESPACE_JSON }}\\ServiceProvider"
            ]@if($SETUP_FACADES),
            "aliases": {
                "{{ $PACKAGE_NAMESPACE }}": "{{ $FULL_NAMESPACE_JSON }}\\Facades\\{{ $PACKAGE_NAMESPACE }}"
            }@endif
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
