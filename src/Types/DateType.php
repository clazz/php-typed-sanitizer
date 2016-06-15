<?php

namespace Clazz\Typed\Types;

class DateType extends StringType
{
    protected $type = 'date';

    const DEFAULT_PATTERN = '/^[1-2][0-9][0-9][0-9]-[0-1]{0,1}[0-9]-[0-3]{0,1}[0-9]$/';

    public function __construct($pattern = null)
    {
        parent::__construct();

        $this->pattern($pattern ?: self::DEFAULT_PATTERN);
    }
}
