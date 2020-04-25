<?php

namespace Yaf\Support\Validation;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ValidationRuleParser
{

    /**
     * Extract the rule name and parameters from a rule.
     *
     * @param  array|string $rules
     * @return array
     */
    public static function parse($rules)
    {
        if (is_array($rules)) {
            $rules = static::parseArrayRule($rules);
        } else {
            $rules = static::parseStringRule($rules);
        }

        $rules[0] = static::normalizeRule($rules[0]);

        return $rules;
    }

    /**
     * Parse an array based rule.
     *
     * @param  array $rules
     * @return array
     */
    protected static function parseArrayRule(array $rules)
    {
        return [Str::studly(trim(Arr::get($rules, 0))), array_slice($rules, 1)];
    }

    /**
     * Parse a string based rule.
     *
     * @param  string $rules
     * @return array
     */
    protected static function parseStringRule($rules)
    {
        $parameters = [];

        // The format for specifying validation rules and parameters follows an
        // easy {rule}:{parameters} formatting convention. For instance the
        // rule "Max:3" states that the value may only be three letters.
        if (strpos($rules, ':') !== false) {
            [$rules, $parameter] = explode(':', $rules, 2);

            $parameters = static::parseParameters($rules, $parameter);
        }

        return [Str::studly(trim($rules)), $parameters];
    }

    /**
     * Parse a parameter list.
     *
     * @param  string $rule
     * @param  string $parameter
     * @return array
     */
    protected static function parseParameters($rule, $parameter)
    {
        $rule = strtolower($rule);

        if (in_array($rule, ['regex', 'not_regex', 'notregex'], true)) {
            return [$parameter];
        }

        return str_getcsv($parameter);
    }

    /**
     * Normalizes a rule so that we can accept short types.
     *
     * @param  string $rule
     * @return string
     */
    protected static function normalizeRule($rule)
    {
        switch ($rule) {
            case 'Int':
                return 'Integer';
            case 'Bool':
                return 'Boolean';
            default:
                return $rule;
        }
    }
}
