@php
    echo '<?php'
@endphp

namespace {{ $FULL_NAMESPACE }}\Http\Controllers{{ $APPEND_NAMESPACE }};

use Illuminate\Http\Request;

use {{ $FULL_NAMESPACE }}\Http\Controllers\BaseController;

class {{ $MAKE_CLASSNAME }} extends BaseController
{
    public function index(Request $request)
    {
        // add your controller methods here
    }
}
