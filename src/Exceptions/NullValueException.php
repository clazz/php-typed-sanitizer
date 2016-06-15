<?php

namespace Clazz\Typed\Exceptions;

class NullValueException extends VerificationFailedException
{
    protected $prompt = '{what}不能为null！';
}
