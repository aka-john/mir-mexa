<?php
namespace Core\Validation\Rules;

class Prnt extends AbstractCtypeRule
{
    protected function ctypeFunction($input)
    {
        return ctype_print($input);
    }
}

