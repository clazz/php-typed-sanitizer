<?php

namespace Clazz\Typed\Exceptions;

class BlankValueException extends VerificationFailedException
{
    protected $prompt = '{what}不能为空！';
}
