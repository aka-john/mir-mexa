<?php
namespace Core\Validation\Rules;

class NotEmpty extends AbstractRule
{
    public function validate($input)
    {
        if (is_string($input)) {
            $input = trim($input);
        }

        return !empty($input);
    }
}

