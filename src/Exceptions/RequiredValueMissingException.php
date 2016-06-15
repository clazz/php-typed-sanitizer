<?php

namespace Clazz\Typed\Exceptions;

class RequiredValueMissingException extends VerificationFailedException
{
    protected $prompt = '缺少必要参数: {what}！';
}
