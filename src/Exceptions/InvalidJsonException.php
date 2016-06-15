<?php

namespace Clazz\Typed\Exceptions;

class InvalidJsonException extends VerificationFailedException
{
    protected $prompt = '{what}不是有效的JSON数据！';
}
