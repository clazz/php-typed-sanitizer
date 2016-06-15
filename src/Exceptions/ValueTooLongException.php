<?php

namespace Clazz\Typed\Exceptions;

class ValueTooLongException extends VerificationFailedException
{
    protected $prompt = '{what}太长了！';
}
