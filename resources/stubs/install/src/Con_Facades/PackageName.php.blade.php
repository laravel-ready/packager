<?php

namespace {{ $FULL_NAMESPACE }}\Facades;

use Illuminate\Support\Facades\Facade;

class {{ $PACKAGE_NAMESPACE }} extends Facade
{
    protected static function getFacadeAccessor()
    {
        return '{{ $PACKAGE_SLUG }}';
    }
}
