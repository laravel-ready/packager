@php
    echo '<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
';
@endphp

Route::name('{{ $PACKAGE_SLUG }}.api.')->prefix('{{ $PACKAGE_SLUG }}')->group(function () {
    // add package-specific api routes here
});
