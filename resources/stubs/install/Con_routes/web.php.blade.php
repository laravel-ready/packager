<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

Route::name('{{ PACKAGE_SLUG }}.web.')->prefix('{{ PACKAGE_SLUG }}')->group(function () {
    // add package-specific web routes here
});
