@php
    echo '<?php'
@endphp

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

Route::name('{{ $PACKAGE_SLUG }}.api.')->prefix('{{ $PACKAGE_SLUG }}')->group(function () {
    // add package-specific api routes here
});
