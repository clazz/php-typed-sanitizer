<?php

namespace Clazz\Typed\Exceptions;

class InvalidFormatException extends VerificationFailedException
{
    protected $prompt = '{what}的格式不正确！';
}
