<?php

namespace Clazz\Typed\Exceptions;

use Clazz\Typed\Types\Type;

class VerificationFailedException extends TypeMissMatchException
{
    protected $prompt = '校验失败！';
    protected $type;
    protected $value;
    public function __construct(Type $type, $value)
    {
        $this->type = $type;
        $this->value = $value;

        if ($type->comment) {
            parent::__construct($type->comment);
        } else {
            parent::__construct(strtr($this->prompt, [
                '{what}' => $this->type->desc ?: $this->type->path,
                '{type}' => $this->type->type,
                '{value}' => json_encode($value),
            ]));
        }
    }
}
