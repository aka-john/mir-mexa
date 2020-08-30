<?php
namespace Core\Validation\Rules;

class NoWhitespace extends AbstractRule
{
    public function validate($input)
    {
        return is_null($input) || !preg_match('#\s#', $input);
    }
}

