<?php

namespace Yaf\Support\Http\Middleware;

use Closure;
use Yaf\Support\Enums\ResultCodeEnum;
use Yaf\Support\Exceptions\SignException;
use Yaf\Support\Http\Request;
use Yaf\Support\Sign\SignDispatcher;

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
     * @throws SignException
     */
    public function handle($request, Closure $next)
    {
        $signResult = $this->checkSign($request);
        if (!$signResult) {
            throw new SignException("sign failure", ResultCodeEnum::SIGN_FAILURE);
        }

        return $next($request);
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function checkSign($request)
    {
        SignDispatcher::distribute($request);

        return true;
    }
}