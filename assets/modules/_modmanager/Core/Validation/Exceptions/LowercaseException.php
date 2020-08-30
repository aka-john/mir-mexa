<?php
namespace Core\Validation\Exceptions;

class LowercaseException extends ValidationException
{
    public static $defaultTemplates = array(
        self::MODE_DEFAULT => array(
            self::STANDARD => '{{name}} must be lowercase',
        ),
        self::MODE_NEGATIVE => array(
            self::STANDARD => '{{name}} must not be lowercase',
        )
    );
}

