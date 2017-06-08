<?php

namespace Clazz\Typed\Exceptions;

use Clazz\Typed\Types\Type;

class VerificationFailedException extends TypeMissMatchException
{
    protected $prompt = '校验失败！';
    protected $type;
    protected $value;

    /**
     * VerificationFailedException constructor.
     *
     * @param Type  $type
     * @param mixed $value
     */
    public function __construct(Type $type, $value='')
    {
        $this->type = $type;
        $this->value = $value;

        if ($type->comment) {
            parent::__construct($type->comment);
        } else {
            parent::__construct(strtr($this->prompt, $this->getPromptParams()));
        }
    }

    protected function getPromptParams()
    {
        return [
            '{what}' => $this->type->desc ?: $this->type->path,
            '{type}' => $this->type->type,
        ];
    }
}
