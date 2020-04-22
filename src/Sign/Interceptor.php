<?php

namespace Yaf\Support\Sign;

use Yaf\Support\Http\Request;

interface Interceptor
{
    /**
     * 校验请求
     * @param Request $request
     * @return mixed
     */
    public function verifyRequest(Request $request);
}