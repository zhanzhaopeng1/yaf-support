<?php

namespace Yaf\Support\Test;

class SignTest
{
    public function testAes()
    {
        $content       = json_encode(['name' => 'test', 'age' => 20]);
        $key           = '123456789';
        $encryptMethod = 'AES-128-CBC';
        $ivLength      = openssl_cipher_iv_length($encryptMethod);
        $iv            = openssl_random_pseudo_bytes($ivLength, $isStrong);
        if (false === $iv && false === $isStrong) {
            die('IV generate failed');
        }

        $res = base64_encode(openssl_encrypt($content, $encryptMethod, $key, 1,$iv));

        var_dump(rtrim(openssl_decrypt(base64_decode($res), $encryptMethod, $key, 1, $iv), "\x00..\x1F"));
    }
}

$s = new SignTest();
$s->testAes();