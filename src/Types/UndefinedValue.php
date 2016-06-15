<?php

namespace Clazz\Typed\Types;

final class UndefinedValue
{
    private function __construct()
    {
        self::$instance = $this;
    }

    private function __clone()
    {
    }

    private function __sleep()
    {
    }

    private function __wakeup()
    {
    }

    public function __toString()
    {
        return '__UNDEFINED__';
    }

    public function __debugInfo()
    {
        return '__UNDEFINED__';
    }

    public function __get($name)
    {
        return $this;
    }

    public function __set($name, $value)
    {
        return $this;
    }

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private static $instance;
}
