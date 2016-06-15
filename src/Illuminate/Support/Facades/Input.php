<?php

namespace Clazz\Typed\Illuminate\Support\Facades;

use Clazz\Typed\Sanitizer;

class Input extends \Illuminate\Support\Facades\Input
{
    /**
     * @param string|array $argument   or $definition
     * @param array|mixed  $definition or $input
     * @param mixed        $input      (default use static::all())
     *
     * @return array
     */
    public static function getSanitized($argument, $definition = [], $input = null)
    {
        if (is_array($argument)) {
            return Sanitizer::getSanitized($argument, $definition ?: static::all());
        } elseif (is_string($argument)) {
            return Sanitizer::getSanitized($argument, $definition, $input ?: static::all());
        } else {
            throw new \InvalidArgumentException('Invalid argument: argument='.json_encode($argument));
        }
    }
}
