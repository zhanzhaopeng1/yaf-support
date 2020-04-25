<?php

namespace Yaf\Support\Validation;

use Exception;

class ValidationException extends Exception
{

    /**
     * ValidationException constructor.
     * @param $message
     * @param $code
     */
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}
