<?php

use LaravelReady\Packager\Exceptions\StrParseException;
use LaravelReady\Packager\Supports\StrSupport;
use LaravelReady\Packager\Exceptions\PhpParseException;

beforeEach(function () {
    $this->strSupport = new StrSupport();
});

test('clean string from special characters', function () {
    expect(
        $this->strSupport::cleanString('123 my-class-name is appController')
    )->toBeString()->toBe('123myclassnameisappController');
});

test('clean string from special characters - failures', function () {
    $this->strSupport::cleanString('==');
})->throws(StrParseException::class, 'Classname cannot be empty');

test('class name failures', function ($className) {
    $this->strSupport::convertToPascalCase($className);
})->with([
    [''],
    ['123'],
])->throws(StrParseException::class, 'Classname cannot be empty');

test('convert class name', function ($className, $expectedValue) {
    expect($this->strSupport::convertToPascalCase($className))
        ->toBeString()
        ->toEqual($expectedValue);
})->with([
    ['myClassName', 'MyClassName'],
    ['my Class_Name_=)Packager', 'MyClassNamePackager'],
    ['123My class Name', 'MyClassName'],
]);

test('slug name failures', function () {
    $this->strSupport::convertToSlug('');
})->throws(StrParseException::class, 'Slug cannot be empty');

test('slug class name', function ($className, $expectedValue) {
    expect($this->strSupport::convertToSlug($className))
        ->toBeString()
        ->toEqual($expectedValue);
})->with([
    ['foo bar baz', 'foo-bar-baz'],
    ['My Awesome Package', 'my-awesome-package'],
]);

test('validate composer package name', function () {
    $result = $this->strSupport::validateComposerPackageName('vendor-name/my-package-name');

    expect($result)->toBeBool()->toBe(true);
});