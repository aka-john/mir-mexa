<?php
namespace Core\Validation\Rules;

class Negative extends AbstractRule
{
    public function validate($input)
    {
        return $input < 0;
    }
}

