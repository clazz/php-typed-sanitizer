<?php

namespace Clazz\Typed\Exceptions;

class ValueTooShortException extends VerificationFailedException
{
    protected $prompt = '{what}太短了！';
}
