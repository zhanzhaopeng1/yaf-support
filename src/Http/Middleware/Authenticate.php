<?php

namespace Yaf\Support\Http\Middleware;

use Closure;
use Yaf\Support\Http\Request;

/**
 * User Auth Middleware
 * Class Authenticate
 * @package App\Middleware
 */
class Authenticate
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param array   ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);

        return $next($request);
    }

    protected function authenticate($request, array $guards)
    {
        //do thing
        var_dump('auth middleware');

        return true;
    }
}