<?php

namespace Yaf\Support\Validation;

use Yaf\Support\Validation\Concerns\ValidatesAttributes;
use Yaf\Support\Validation\Contracts\Validator as ValidatorContract;

class Validator implements ValidatorContract
{
    use ValidatesAttributes;

    /**
     * validate params
     *
     * @example ['age'=>'required|int|between:1,100']
     *
     * @param array $rules
     * @return bool
     * @throws ValidationException
     */
    public function validate(array $rules)
    {
        if (empty($rules)) {
            return true;
        }

        foreach ($rules as $attribute => $rule) {

            $ruleList = explode('|', $rule);

            foreach ($ruleList as $value) {
                $this->validateAttribute($attribute, $value);
            }
        }

        return true;
    }

    /**
     * @param $attribute
     * @param $rule
     * @throws ValidationException
     */
    protected function validateAttribute($attribute, $rule)
    {
        [$rule, $parameters] = ValidationRuleParser::parse($rule);

        if ($rule == '') {
            return;
        }

        $value = request()->getParam($attribute);

        $method = "validate{$rule}";

        if (!$this->$method($attribute, $value, $parameters, $this)) {

            throw new ValidationException("The {$attribute} given {$rule} data was invalid.", 1003);
        }
    }

}