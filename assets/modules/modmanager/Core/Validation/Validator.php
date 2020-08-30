<?php
namespace Core\Validation;

use ReflectionClass;
use ReflectionException;
use Core\Validation\Exceptions\AllOfException;
use Core\Validation\Exceptions\ComponentException;
use Core\Validation\Rules\AllOf;

/**
 * @method \Core\Validation\Validator allOf()
 * @method \Core\Validation\Validator alnum(string $additionalChars = null)
 * @method \Core\Validation\Validator alpha(string $additionalChars = null)
 * @method \Core\Validation\Validator alwaysInvalid()
 * @method \Core\Validation\Validator alwaysValid()
 * @method \Core\Validation\Validator arr()
 * @method \Core\Validation\Validator attribute(string $reference, Validatable $validator = null, bool $mandatory = true)
 * @method \Core\Validation\Validator base()
 * @method \Core\Validation\Validator between(int $min = null, int $max = null, bool $inclusive = false)
 * @method \Core\Validation\Validator bool()
 * @method \Core\Validation\Validator call()
 * @method \Core\Validation\Validator callback(mixed $callback)
 * @method \Core\Validation\Validator charset(array $charset)
 * @method \Core\Validation\Validator cnh()
 * @method \Core\Validation\Validator cnpj()
 * @method \Core\Validation\Validator consonant(string $additionalChars = null)
 * @method \Core\Validation\Validator contains(mixed $containsValue, bool $identical = false)
 * @method \Core\Validation\Validator countryCode()
 * @method \Core\Validation\Validator cpf()
 * @method \Core\Validation\Validator creditCard()
 * @method \Core\Validation\Validator date(string $format = null)
 * @method \Core\Validation\Validator digit(string $additionalChars = null)
 * @method \Core\Validation\Validator directory()
 * @method \Core\Validation\Validator domain()
 * @method \Core\Validation\Validator each(Validatable $itemValidator = null, Validatable $keyValidator = null)
 * @method \Core\Validation\Validator email()
 * @method \Core\Validation\Validator endsWith(mixed $endValue, bool $identical = false)
 * @method \Core\Validation\Validator equals(mixed $compareTo, bool $compareIdentical=false)
 * @method \Core\Validation\Validator even()
 * @method \Core\Validation\Validator exists()
 * @method \Core\Validation\Validator file()
 * @method \Core\Validation\Validator float()
 * @method \Core\Validation\Validator graph(string $additionalChars = null)
 * @method \Core\Validation\Validator in(array $haystack, bool $compareIdentical = false)
 * @method \Core\Validation\Validator instance(string $instanceName)
 * @method \Core\Validation\Validator int()
 * @method \Core\Validation\Validator ip(array $ipOptions = null)
 * @method \Core\Validation\Validator json()
 * @method \Core\Validation\Validator key(string $reference, Validatable $referenceValidator = null, bool $mandatory = true)
 * @method \Core\Validation\Validator leapDate(mixed $format)
 * @method \Core\Validation\Validator leapYear()
 * @method \Core\Validation\Validator length(int $min=null, int $max=null, bool $inclusive = true)
 * @method \Core\Validation\Validator lowercase()
 * @method \Core\Validation\Validator macAddress()
 * @method \Core\Validation\Validator max(int $maxValue, bool $inclusive = false)
 * @method \Core\Validation\Validator min(int $minValue, bool $inclusive = false)
 * @method \Core\Validation\Validator minimumAge(int $age)
 * @method \Core\Validation\Validator multiple(int $multipleOf)
 * @method \Core\Validation\Validator negative()
 * @method \Core\Validation\Validator noneOf()
 * @method \Core\Validation\Validator not(Validatable $rule)
 * @method \Core\Validation\Validator notEmpty()
 * @method \Core\Validation\Validator noWhitespace()
 * @method \Core\Validation\Validator nullValue()
 * @method \Core\Validation\Validator numeric()
 * @method \Core\Validation\Validator object()
 * @method \Core\Validation\Validator odd()
 * @method \Core\Validation\Validator oneOf()
 * @method \Core\Validation\Validator perfectSquare()
 * @method \Core\Validation\Validator phone()
 * @method \Core\Validation\Validator positive()
 * @method \Core\Validation\Validator primeNumber()
 * @method \Core\Validation\Validator prnt(string $additionalChars = null)
 * @method \Core\Validation\Validator punct(string $additionalChars = null)
 * @method \Core\Validation\Validator readable()
 * @method \Core\Validation\Validator regex($regex)
 * @method \Core\Validation\Validator roman()
 * @method \Core\Validation\Validator sf(string $name, array $params = null)
 * @method \Core\Validation\Validator slug()
 * @method \Core\Validation\Validator space(string $additionalChars = null)
 * @method \Core\Validation\Validator startsWith(mixed $startValue, bool $identical = false)
 * @method \Core\Validation\Validator string()
 * @method \Core\Validation\Validator symbolicLink()
 * @method \Core\Validation\Validator tld()
 * @method \Core\Validation\Validator uploaded()
 * @method \Core\Validation\Validator uppercase()
 * @method \Core\Validation\Validator version()
 * @method \Core\Validation\Validator vowel()
 * @method \Core\Validation\Validator when(Validatable $if, Validatable $then, Validatable $when)
 * @method \Core\Validation\Validator writable()
 * @method \Core\Validation\Validator xdigit(string $additionalChars = null)
 * @method \Core\Validation\Validator zend(mixed $validator, array $params = null)
 */
class Validator extends AllOf
{

    public static function __callStatic($ruleName, $arguments)
    {
        if ('allOf' === $ruleName) {
            return static::buildRule($ruleName, $arguments);
        }

        $validator = new static;

        return $validator->__call($ruleName, $arguments);
    }

    public static function buildRule($ruleSpec, $arguments=array())
    {
        if ($ruleSpec instanceof Validatable) {
            return $ruleSpec;
        }

        try {
            $validatorFqn = '\Core\\Validation\\Rules\\' . ucfirst($ruleSpec);

            $validatorClass = new ReflectionClass($validatorFqn);

            $validatorInstance = $validatorClass->newInstanceArgs(
                    $arguments
            );

            return $validatorInstance;
        } catch (ReflectionException $e) {
            throw new ComponentException($e->getMessage());
        }
    }

    public function __call($method, $arguments)
    {
        if ('not' === $method) {
            return $arguments ? static::buildRule($method, $arguments) : new Rules\Not($this);
        }

        if (isset($method{4}) &&
            substr($method, 0, 4) == 'base' && preg_match('@^base([0-9]{1,2})$@', $method, $match)) {
            return $this->addRule(static::buildRule('base', array($match[1])));
        }

        return $this->addRule(static::buildRule($method, $arguments));
    }

    public function reportError($input, array $extraParams=array())
    {
        $exception = new AllOfException;
        $input = AllOfException::stringify($input);
        $name = $this->getName() ? : "\"$input\"";
        $params = array_merge(
            $extraParams, get_object_vars($this), get_class_vars(__CLASS__)
        );
        $exception->configure($name, $params);
        if (!is_null($this->template)) {
            $exception->setTemplate($this->template);
        }

        return $exception;
    }

    /**
     * Create instance validator
     *
     * @static
     * @return \Core\Validation\Validator
     */
    public static function create()
    {
        $ref = new ReflectionClass(__CLASS__);

        return $ref->newInstanceArgs(func_get_args());
    }
}

