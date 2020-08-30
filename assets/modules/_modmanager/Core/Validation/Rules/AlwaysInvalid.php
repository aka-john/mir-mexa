<?php
namespace Core\Validation\Rules;

class AlwaysInvalid extends AbstractRule
{
    public function validate($input)
    {
        return false;
    }
}

