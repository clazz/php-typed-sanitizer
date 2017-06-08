<?php

namespace Clazz\Typed\Types;

use Clazz\Typed\Exceptions\InvalidTypeException;
use Clazz\Typed\Exceptions\TooManyFieldsException;

/**
 * @property $fields array
 * @property $isList bool
 */
class ArrayType extends Type
{
    protected $type = 'array';
    protected $isList = false;
    protected $noExtraFields = false;
    protected $fields = [];
    protected $defaultValue = [];

    public function __construct($fields = [])
    {
        $this->fields($fields);
    }

    /**
     * 说明是一个列表 -- 即只有数字类型的key，并且只有一种类型的内容.
     *
     * @return $this
     */
    public function isList()
    {
        $this->isList = true;

        return $this;
    }

    public function isListOf($type)
    {
        $this->isList();

        return $this->field('*', $type);
    }

    public function listOf($type)
    {
        return $this->isListOf($type);
    }

    /**
     * 没有其他key(仅适用于非list方式的array)
     * @return $this
     */
    public function noExtraFields()
    {
        $this->noExtraFields = true;
        return $this;
    }

    /**
     * 没有其他key(仅适用于非list方式的array)
     * @return $this
     */
    public function hasNoExtraFields()
    {
        return $this->noExtraFields();
    }

    public function define($name, $definition = 'string')
    {
        return $this->field($name, $definition);
    }

    public function fields($fields)
    {
        foreach ($fields as $fieldName => $definition) {
            $this->field($fieldName, $definition);
        }

        return $this;
    }

    public function field($name, $type = 'string')
    {
        if (isset($this->fields[$name])) {
            throw new \InvalidArgumentException("Duplicated definition: $name.");
        }

        if ($this->isList && !empty($this->fields)) {
            throw new \InvalidArgumentException('Cannot add more than one field to a list!');
        }

        $this->fields[$name] = $type instanceof Type ? $type : Type::of($type);

        return $this;
    }

    public function sanitize($value)
    {
        $this->hasDefaultValue = $this->getHasDefaultValue();

        return parent::sanitize($value);
    }

    public function getHasDefaultValue()
    {
        if ($this->hasDefaultValue) {
            return true;
        }

        foreach ($this->fields as $field) {
            if ($field->hasDefaultValue) {
                return true;
            }
        }

        return false;
    }

    protected function beforeApplyRules($value)
    {
        if ($value == Type::undefinedValue()) {
            return $value;
        }

        if (is_object($value)) {
            $value = (array) $value;
        }

        if (!is_array($value)) {
            throw new InvalidTypeException($this, $value);
        }

        $result = [];

        if ($this->isList) {
            $fieldType = reset($this->fields);
            if (empty($fieldType)) {
                throw new \InvalidArgumentException('A list must have a field!');
            }

            foreach ($value as $index => $item) {
                $result[$index] = $fieldType->sanitize($item);
            }
        } else {
            if ($this->noExtraFields){
                $extraFields = array_diff(array_keys($value), array_keys($this->fields));
                if (!empty($extraFields)){
                    throw new TooManyFieldsException($this, $value, $extraFields);
                }
            }

            foreach ($this->fields as $fieldName => $fieldType) {
                $fieldType->path = ($this->path ? $this->path.'.' : '').$fieldName;
                $fieldValue = array_key_exists($fieldName, $value) ? $value[$fieldName] : Type::undefinedValue();
                $fieldValue = $fieldType->sanitize($fieldValue);
                if ($fieldValue !== Type::undefinedValue()) {
                    $result[$fieldName] = $fieldValue;
                }
            }
        }

        return $result;
    }

    public function __toString()
    {
        $str = parent::__toString();
        $fieldsLines = [];

        $maxFieldNameLen = max(array_map('strlen', array_keys($this->fields)));

        foreach ($this->fields as $fieldName => $fieldType) {
            $fieldDef = str_pad($fieldName, $maxFieldNameLen, ' ').' => '.$fieldType;
            $fieldsLines = array_merge($fieldsLines, explode("\n", $fieldDef));
        }

        $fieldsStr = implode("\n  ", $fieldsLines);
        if ($this->isList) {
            return $str." List([\n  ".$fieldsStr."\n])";
        } else {
            return $str." Map({\n  ".$fieldsStr."\n})";
        }
    }
}
