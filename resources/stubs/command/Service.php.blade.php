@php
    echo '<?php'
@endphp

namespace {{ $FULL_NAMESPACE }}\Services{{ $APPEND_NAMESPACE }};

class {{ MAKE_CLASSNAME }}
{
    public function __construct()
    {
    }

    public function sayHello()
    {
        return 'Hello World!';
    }
}
