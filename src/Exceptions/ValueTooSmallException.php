<?php

namespace Clazz\Typed\Exceptions;

class ValueTooSmallException extends VerificationFailedException
{
    protected $prompt = '{what}太小了！请务必小于{value}';

    protected function getPromptParams()
    {
        $params = parent::getPromptParams();
        $params['{value}'] = $this->value;
        return $params;
    }
}
