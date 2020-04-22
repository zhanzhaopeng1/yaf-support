<?php

namespace Yaf\Support\Sign;

use InvalidArgumentException;
use Yaf\Support\Http\Request;

/**
 * 接口拦截器分发器
 *
 * Class SignDispatcher
 * @package Yaf\Support\Sign
 */
class SignDispatcher
{
    /**
     * @param Request $request
     */
    public static function distribute(Request $request)
    {
        $interceptorClass = $request->getSign();

        if (!empty($interceptorClass)) {
            try {
                $object = new $interceptorClass;
            } catch (\Exception $exception) {
                throw new InvalidArgumentException("无效的请求", -1);
            }
        } else {
            $object = new NotCheckResquest();
        }

        $object->verifyRequest($request);
    }
}