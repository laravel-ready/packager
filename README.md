# Packager

[![Packager](https://preview.dragon-code.pro/laravel-ready/packager.svg?brand=laravel)](https://github.com/laravel-readypackager)

[![Stable Version][badge_stable]][link_packagist]
[![Unstable Version][badge_unstable]][link_packagist]
[![Total Downloads][badge_downloads]][link_packagist]
[![License][badge_license]][link_license]

## 📂 About

Currently, Laravel does not provide a package *creation / generation / wizard* tool. Packager is a package creation tool package. You can create laravel packages with this tool on CLI easily. Generally, we use singleton packages for developing laravel packages, or we often craft the packages manually. This takes some time and it process is open to errors. Packager generates all files from templates and accelerates the development phase.

Packager follows [PSR](https://www.php-fig.org/psr/) standards, [laravel API](https://laravel.com/api/9.x/) and laravel [folder structure](https://github.com/laravel/laravel).

This package is highly inspired by [yediyuz/laravel-package](https://github.com/yediyuz/laravel-package)

## 📦 Installation

Get via composer

`composer require laravel-ready/packager --dev`

or install globally

`composer global require laravel-ready/packager --dev`

[badge_downloads]:      https://img.shields.io/packagist/dt/laravel-ready/packager.svg?style=flat-square

[badge_license]:        https://img.shields.io/packagist/l/laravel-ready/packager.svg?style=flat-square

[badge_stable]:         https://img.shields.io/github/v/release/laravel-ready/packager?label=stable&style=flat-square

[badge_unstable]:       https://img.shields.io/badge/unstable-dev--main-orange?style=flat-square

[link_license]:         LICENSE

[link_packagist]:       https://packagist.org/packages/laravel-ready/packager
