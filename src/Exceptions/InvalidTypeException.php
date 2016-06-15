<?php

namespace Clazz\Typed\Exceptions;

class InvalidTypeException extends VerificationFailedException
{
    protected $prompt = '{what}的类型错误！必须是{type}类型！';
}
