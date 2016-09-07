<?php

namespace Clazz\Typed\Types;

class DateTimeType extends StringType
{
    protected $type = 'datetime';

    const DEFAULT_PATTERN = '/^[1-2][0-9][0-9][0-9]-[0-1]{0,1}[0-9]-[0-3]{0,1}[0-9] (([0-1][0-9])|(2[0-4])):[0-6][0-9](:[0-6][0-9])?$/';

    public function __construct($pattern = null)
    {
        parent::__construct();

        $this->pattern($pattern ?: self::DEFAULT_PATTERN);
    }
}
