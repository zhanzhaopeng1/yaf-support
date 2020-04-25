<?php

namespace Yaf\Support\Validation\Contracts;

interface Validator
{
    /**
     * validate params
     *
     * @param array $rules
     * @return mixed
     */
    public function validate(array $rules);
}