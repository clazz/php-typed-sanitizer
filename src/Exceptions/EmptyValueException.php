<?php

namespace Clazz\Typed\Exceptions;

class EmptyValueException extends VerificationFailedException
{
    protected $prompt = '{what}不能为空！';
}
