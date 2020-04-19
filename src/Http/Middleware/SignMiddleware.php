<?php

namespace Yaf\Support\Http\Middleware;

use Closure;
use Yaf\Support\Enums\ResultCodeEnum;
use Yaf\Support\Exceptions\SignExceptions;
use Yaf\Support\Http\Request;

/**
 * Sign Middleware
 * Class SignMiddleware
 * @package App\Middleware
 */
class SignMiddleware
{
    /**
     * @param  Request $request
     * @param Closure  $next
     * @return mixed
     * @throws SignExceptions
     */
    public function handle($request, Closure $next)
    {
        $signResult = $this->checkSign($request);
        if (!$signResult) {
            throw new SignExceptions("sign failure", ResultCodeEnum::SIGN_FAILURE);
        }

        return $next($request);
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function checkSign($request)
    {
        // do thing
        var_dump('sign middleware');

        return true;
    }
}