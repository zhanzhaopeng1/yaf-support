<?php

namespace Yaf\Support\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Yaf\Support\Auth\AuthenticationException;

/**
 * User Auth Middleware
 * Class Authenticate
 * @package App\Middleware
 */
class Authenticate
{
    /**
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Authenticate constructor.
     */
    public function __construct()
    {
        $this->auth = app(AuthFactory::class);
    }

    /**
     * @param         $request
     * @param Closure $next
     * @param array   ...$guards
     * @return mixed
     * @throws AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);

        return $next($request);
    }

    /**
     * @param       $request
     * @param array $guards
     * @return bool
     * @throws AuthenticationException
     */
    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }

        throw new AuthenticationException(
            'Unauthenticated.', $guards
        );
    }
}