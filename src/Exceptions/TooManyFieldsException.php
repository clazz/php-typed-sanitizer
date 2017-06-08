<?php

namespace Clazz\Typed\Exceptions;

use Clazz\Typed\Types\Type;

class TooManyFieldsException extends VerificationFailedException
{
    protected $prompt = '{what}的字段太多了！多余的字段：{value}';
    protected $extraFields;

    public function __construct(Type $type, $value, $extraFields)
    {
        $this->extraFields = $extraFields;
        parent::__construct($type, $value);
    }

    protected function getPromptParams()
    {
        return [
            '{what}' => $this->type->desc ?: $this->type->path,
            '{extraFields}' => is_array($this->extraFields) ? implode(', ', $this->extraFields) : $this->extraFields,
        ];
    }
}
