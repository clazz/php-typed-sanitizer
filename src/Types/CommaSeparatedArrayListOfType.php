<?php


namespace Clazz\Typed\Types;


class CommaSeparatedArrayListOfType extends ArrayType
{
    protected $type = 'CSA';
    protected $delimiter;
    protected $enclosure;
    protected $escape;

    public function __construct($type)
    {
        parent::__construct();

        $this->isListOf($type)->defaultValue([]);
    }

    protected function beforeApplyRules($value)
    {
        $value = is_array($value) ? $value : str_getcsv($value, $this->delimiter, $this->enclosure, $this->escape);
        return parent::beforeApplyRules($value);
    }
}