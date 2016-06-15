<?php

namespace Clazz\Typed\Exceptions;

class ValueTooBigException extends VerificationFailedException
{
    protected $prompt = '{what}太大了！请务必小于{value}';
}
