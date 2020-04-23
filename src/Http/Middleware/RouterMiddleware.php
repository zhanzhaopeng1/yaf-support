<?php

namespace Yaf\Support\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use Yaf\Support\Http\Request;
use InvalidArgumentException;

class RouterMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->getShouldMethod() !== Str::lower($request->getMethod())
            && $request->getShouldMethod() !== 'any') {

            throw new InvalidArgumentException('invalid request method', -1);
        }

        return $next($request);
    }
}