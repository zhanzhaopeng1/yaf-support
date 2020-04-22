<?php

namespace Yaf\Support\Sign;

use Yaf\Support\Enums\ResultCodeEnum;
use Yaf\Support\Exceptions\InvalidParameterException;
use Yaf\Support\Exceptions\SignException;
use Yaf\Support\Http\Request;

/**
 * aes 解密校验参数
 *
 * Class AesDecryptRequest
 * @package Yaf\Support\Sign
 */
class AesDecryptRequest implements Interceptor
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

        $data     = $param['data'];
        $rawInput = $this->aesDecrypt(base64_decode($data), config()->sign->aes->key);

        $info = json_decode($rawInput, true);
        if (empty($info)) {
            throw new SignException("sign failure", ResultCodeEnum::SIGN_FAILURE);
        }

        $request->setParam('decrypt_data', $info);
    }

    /**
     * @param $content
     * @param $key
     * @return string
     */
    public function aesEncrypt($content, $key)
    {
        return openssl_encrypt($content, 'AES-128-CBC', $key, 1);
    }

    /**
     * @param $content
     * @param $key
     * @return string
     */
    public function aesDecrypt($content, $key)
    {
        return rtrim(openssl_decrypt(base64_decode($content), 'AES-128-CBC', $key, 1, $key), "\x00..\x1F");
    }
}