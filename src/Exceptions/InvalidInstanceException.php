<?php

namespace Clazz\Typed\Exceptions;

class InvalidInstanceException extends VerificationFailedException
{
    protected $prompt = '{what}的类型不正确！应该是{type}类型！';
}
