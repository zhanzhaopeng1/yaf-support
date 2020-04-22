<?php

namespace Yaf\Support\Sign;

use Yaf\Support\Enums\ResultCodeEnum;
use Yaf\Support\Exceptions\InvalidParameterException;
use Yaf\Support\Http\Request;
use Yaf\Support\Exceptions\SignException;

/**
 * md5 sign校验参数
 *
 * Class Md5SignCheckRequest
 * @package Yaf\Support\Sign
 */
class Md5SignCheckRequest implements Interceptor
{

    /**
     * @param Request $request
     * @return mixed|void
     * @throws InvalidParameterException
     * @throws SignException
     */
    public function verifyRequest(Request $request)
    {
        $param = json_decode($request->getRawInput(), true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidParameterException('invalid param', -1);
        }

        $data = $param['data'];
        $sign = $param['sign'];

        $salt = config()->sign->md5->salt;
        if (md5($data . $salt) != $sign) {
            throw new SignException("sign failure", ResultCodeEnum::SIGN_FAILURE);
        }

        $request->setParam('data', json_decode($data, true));
        $request->setParam('sign', $sign);
    }
}