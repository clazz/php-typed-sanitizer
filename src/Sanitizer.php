<?php

namespace Clazz\Typed;

class Sanitizer
{
    /**
     * @param string|array $argument   or $definition
     * @param array|mixed  $definition or $input
     * @param mixed        $input
     *
     * @return array
     */
    public static function getSanitized($argument, $definition = [], $input = null)
    {
        $sanitizer = new Types\ArrayType();

        if (is_array($argument)) {
            $input = $definition;
            foreach ($argument as $key => $def) {
                $sanitizer->define($key, $def);
            }

            return $sanitizer->sanitize($input);
        } elseif (is_string($argument)) {
            $sanitizer->define($argument, $definition);
            $sanitized = $sanitizer->sanitize($input);

            return isset($sanitized[$argument]) ? $sanitized[$argument] : null;
        } else {
            throw new \InvalidArgumentException('Invalid argument: argument='.json_encode($argument));
        }
    }
}
