<?php
namespace Core\Validation\Rules;

class String extends AbstractRule
{
    public function validate($input)
    {
        return is_string($input);
    }
}

