<?php
namespace Core\Validation\Rules;

class Int extends AbstractRule
{
    public function validate($input)
    {
        return is_numeric($input) && (int) $input == $input;
    }
}

