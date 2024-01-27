@php
    echo '<?php'
@endphp

namespace {{ $FULL_NAMESPACE }}\Exceptions;

use Exception;

final class {{ $PACKAGE_NAMESPACE }}Exception extends Exception
{
}
