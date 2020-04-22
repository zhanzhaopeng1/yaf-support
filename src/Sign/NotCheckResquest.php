<?php

namespace Yaf\Support\Sign;

use Yaf\Support\Http\Request;

/**
 * 没有校验任何参数的过滤器
 *
 * Class NotCheckResquest
 * @package Yaf\Support\Sign
 */
class NotCheckResquest implements Interceptor
{

    /**
     * @param Request $request
     * @return mixed|void
     */
    public function verifyRequest(Request $request)
    {
        // do noting
        echo "not check sign";
    }

}