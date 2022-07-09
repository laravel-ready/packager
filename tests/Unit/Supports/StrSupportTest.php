<?php

use LaravelReady\Packager\Supports\StrSupport;

$className = new StrSupport();

test('correct class name', function () use ($className) {
    $result = $className->convertToPascalCase('123 my-class-name is appController');

    expect($result)->toBeString()->toBe('MyClassNameIsAppController');
});

test('convert class name to slug', function () use ($className) {
    $result = $className->convertToSlug('MyClassNameIsAppController');

    expect($result)->toBeString()->toBe('my-class-name-is-app-controller');
});

test('clean string from special characters', function () use ($className) {
    $result = $className->cleanString('123 my-class-name is appController');

    expect($result)->toBeString()->toBe('123myclassnameisappController');
});

test('validate composer package name', function () use ($className) {
    $result = $className->validateComposerPackageName('vendor-name/my-package-name');

    expect($result)->toBeBool()->toBe(true);
});