@php
    echo '<?php'
@endphp

namespace {{ $FULL_NAMESPACE }}\Http\Middleware;

use Closure;

use Illuminate\Http\Request;

class {{ $MAKE_CLASSNAME }}Middleware
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
