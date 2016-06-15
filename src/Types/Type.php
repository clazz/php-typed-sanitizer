<?php

namespace Clazz\Typed\Types;

use Clazz\Typed\Exceptions\BlankValueException;
use Clazz\Typed\Exceptions\EmptyValueException;
use Clazz\Typed\Exceptions\InvalidFormatException;
use Clazz\Typed\Exceptions\NullValueException;
use Clazz\Typed\Exceptions\RequiredValueMissingException;
use Clazz\Typed\Exceptions\ValueTooLongException;
use Clazz\Typed\Exceptions\ValueTooShortException;

/**
 * @method static StringType  str()
 * @method static StringType  string()
 * @method static IntegerType int()
 * @method static IntegerType integer()
 * @method static FloatType   float()
 * @method static DoubleType   double()
 * @method static ArrayType   array_($fields = [])         // `array`是关键词，不能用作函数名，囧
 * @method static ArrayType   arr($fields = [])
 * @method static BooleanType boolean()
 * @method static BooleanType bool()
 * @method static DateType    date()
 * @method static TimeType    time()
 * @method static DateTimeType datetime()
 * @method static UrlType     url()
 * @method static AnyType     any()
 * @method static MobilePhoneType   phone()  // 手机号码
 * @method static MobilePhoneType   mobile() // 手机号码
 * @method static MobilePhoneType   mobilePhone() // 手机号码
 * @method static JsonType         json($decodedType)
 * @method static JsonArrayType    jsonArray($fields=[])
 * @method static JsonObjectType   jsonObject($fields=[])
 * @method static OneOfType        oneOf($types)
 * @method static ConstantType     constant($value)     // 常量类型
 * @method $this split
 * @method $this explode($delimiter)
 * @method $this implode($glue)
 * @method $this ucfirst
 * @method $this lcfirst
 * @method $this str_toupper
 * @method $this str_tolower
 *
 * @property $type string
 * @property $path string
 */
class Type
{
    protected $type = 'UNKNOWN';
    protected $path = '';
    protected $desc = '';
    protected $comment = '';
    protected $isRequired = true;
    protected $hasDefaultValue = false;
    protected $defaultValue = null;
    protected $minLength = null;
    protected $maxLength = null;
    protected $isNotBlank = false;
    protected $isNotEmpty = false;
    protected $isNotNull = false;
    protected $rules = [];
    protected $prompt = 'Invalid argument';

    protected static $alias = [
        'int' => 'Integer',
        'array_' => 'Array',
        'arr' => 'Array',
        'uint' => 'UnsignedInteger',
        'unsigned' => 'UnsignedInteger',
        'bool' => 'Boolean',
        'str' => 'String',
        'datetime' => 'DateTime',
        'phone' => 'MobilePhone',
        'mobile' => 'MobilePhone',
    ];

    public function __construct()
    {
    }

    public static function __callStatic($name, $arguments)
    {
        return static::of($name, $arguments);
    }

    public static function of($type, $arguments = [])
    {
        if (is_array($type)) {
            return self::of('arr', [$type]);
        }

        if (isset(self::$alias[$type])) {
            $type = self::$alias[$type];
        }

        $typeClass = __NAMESPACE__.'\\'.ucfirst($type).'Type';
        if (class_exists($typeClass)) {
            $typeClass = new \ReflectionClass($typeClass);

            return $typeClass->newInstanceArgs($arguments);
        }

        throw new \BadMethodCallException("$type is not defined!");
    }

    public static function arrayListOf($type)
    {
        return static::arr()->listOf($type);
    }

    public static function undefinedValue()
    {
        return UndefinedValue::instance();
    }

    /**
     * @return static
     */
    public static function create()
    {
        $class = new \ReflectionClass(static::class);
        $constructor = $class->getConstructor();
        if ($constructor->getNumberOfParameters() < func_num_args()) {
            throw new \BadMethodCallException('Too many arguments!');
        }

        return $class->newInstanceArgs(func_get_args());
    }

    public function __get($name)
    {
        $getter = 'get'.$name;
        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        }

        if (property_exists($this, $name)) {
            return $this->$name;
        }

        throw new \BadMethodCallException("$name does not exist");
    }

    public function __set($name, $value)
    {
        $setter = 'set'.$name;
        if (method_exists($this, $setter)) {
            $this->{$setter}($value);

            return $value;
        }

        if (property_exists($this, $name)) {
            $this->$name = $value;

            return $value;
        }

        throw new \BadMethodCallException("$name does not exist");
    }

    /**
     * 描述信息！描述这个字段是干嘛的。
     *
     * @param $desc
     *
     * @return $this
     */
    public function desc($desc)
    {
        $this->desc = $desc;

        return $this;
    }

    /**
     * 注释信息：说明这个字段的格式和要求！
     *
     * @param $comment
     *
     * @return $this
     */
    public function comment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    public function isRequired()
    {
        $this->isRequired = true;

        return $this;
    }

    public function isOptional()
    {
        $this->isRequired = false;

        return $this;
    }

    /**
     * 非空白.
     *
     * @return $this
     */
    public function isNotBlank()
    {
        $this->isNotBlank = true;

        $this->addRule(function ($value) {
            if ($this->isItBlank($value)) {
                throw new BlankValueException($this, $value);
            }

            return $value;
        });

        return $this;
    }

    /**
     * 非空白.
     *
     * @return $this
     */
    public function notBlank()
    {
        return $this->isNotBlank();
    }

    protected function isItBlank($value)
    {
        return preg_match('/^\s*$/', $value);
    }

    /**
     * 非空.
     *
     * @return $this
     */
    public function isNotEmpty()
    {
        $this->isNotEmpty = true;

        $this->addRule(function ($value) {
            if (empty($value)) {
                throw new EmptyValueException($this, $value);
            }

            return $value;
        });

        return $this;
    }

    /**
     * 非空.
     *
     * @return $this
     */
    public function notEmpty()
    {
        return $this->isNotEmpty();
    }

    /**
     * 非null.
     *
     * @return $this
     */
    public function isNotNull()
    {
        $this->isNotNull = true;

        $this->addRule(function ($value) {
            if (is_null($value)) {
                throw new NullValueException($this, $value);
            }

            return $value;
        });

        return $this;
    }

    /**
     * 非null.
     *
     * @return $this
     */
    public function notNull()
    {
        return $this->isNotNull();
    }

    public function isNotBlankOrEmpty()
    {
        return $this->isNotEmpty()->isNotBlank();
    }

    public function defaultValue($value)
    {
        $this->hasDefaultValue = true;
        $this->defaultValue = $value;

        return $this;
    }

    public function __call($name, $arguments)
    {
        if (is_callable($name)) {
            if (empty($arguments)) {
                $this->addRule($name);
            } else {
                $this->addRule(function ($value) use ($name, $arguments) {
                    $arguments[] = $value;

                    return call_user_func_array($name, $arguments);
                });
            }

            return $this;
        }

        throw new \BadMethodCallException("Unknown method or rule: $name");
    }

    public function trim()
    {
        $this->addRule(function ($value) {
            return trim($value);
        });

        return $this;
    }

    /**
     * 设置长度，支持一下类型：
     * length(10, 20)     => 表示最小长度10(含)，最大长度20(含)
     * length(10)         => 表示最大长度10(含)
     * length('10 ~ 20')  => 表示最小长度10(含)，最大长度20(含)
     * length('10 - 20')  => 表示最小长度10(含)，最大长度20(含)
     * length('> 9')      => 表示最小长度10 (即9 + 1)
     * length('< 21')     => 表示最大长度20 (即21 - 1)
     * length('>= 10')    => 表示最小长度10(含)
     * length('<= 20')    => 表示最大长度20(含).
     *
     * @param string|int $min
     * @param int        $max
     *
     * @return $this
     */
    public function length($min, $max = null)
    {
        $args = func_get_args();
        if (empty($args)) {
            throw new \InvalidArgumentException('Too few arguments!');
        }

        $arg = $args[0];

        if (count($args) > 2) {
            throw new \InvalidArgumentException('Too many arguments!');
        } elseif (count($args) == 2) {
            if (!is_numeric($min) || !is_numeric($max)) {
                throw new \InvalidArgumentException('Invalid numeric arguments: '.json_encode($args));
            }

            $this->minLength = $min;
            $this->maxLength = $max;
        } elseif (is_numeric($arg)) {
            $this->maxLength = intval($arg);
        } elseif (preg_match('/^\s*(?<a>\d+)\s*(?<op>~|-)\s*(?<b>\d+)\s*$/', $arg, $m)) {
            if ($m['a'] >= $m['b']) {
                throw new \InvalidArgumentException("Invalid length definition: $arg");
            }

            $this->minLength = $m['a'];
            $this->maxLength = $m['b'];
        } elseif (preg_match('/^\s*(?<op><|>|<=|>=)\s*(?<a>\d+)\s*$/', $arg, $m)) {
            if ($m['op'] == '<') {
                $this->maxLength = $m['a'] - 1;
            } elseif ($m['op'] == '>') {
                $this->minLength = $m['a'] + 1;
            } elseif ($m['op'] == '<=') {
                $this->maxLength = $m['a'];
            } elseif ($m['op'] == '>=') {
                $this->minLength = $m['a'];
            } else {
                throw new \InvalidArgumentException('Invalid length definition: '.$arg);
            }
        } else {
            throw new \InvalidArgumentException('Invalid length definition: '.$arg);
        }

        $this->addRule(function ($value) {
            if (!is_null($this->minLength) || !is_null($this->maxLength)) {
                $valueLen = mb_strlen($value);
                if (!is_null($this->minLength) && $valueLen < $this->minLength) {
                    throw new ValueTooShortException($this, $value);
                }

                if (!is_null($this->maxLength) && $valueLen > $this->maxLength) {
                    throw new ValueTooLongException($this, $value);
                }
            }

            return $value;
        });

        return $this;
    }

    public function matches($regPattern)
    {
        return $this->pattern($regPattern);
    }

    public function pattern($regPattern)
    {
        return $this->addRule(function ($value) use ($regPattern) {
            if (!preg_match($regPattern, $value)) {
                throw new InvalidFormatException($this, $value);
            }

            return $value;
        });
    }

    public function isInArray($arr)
    {
        return $this->addRule(function ($value) use ($arr) {
            if (!in_array($value, $arr)) {
                throw new InvalidFormatException($this, $value);
            }

            return $value;
        });
    }

    public function inArray($arr)
    {
        return $this->isInArray($arr);
    }

    public function stripTags()
    {
        $args = func_get_args();

        return $this->addRule(function ($value) use ($args) {
            array_unshift($args, $value);

            return call_user_func_array('strip_tags', $args);
        });
    }

    public function toUpper()
    {
        return $this->addRule('strtoupper');
    }

    public function toLower()
    {
        return $this->addRule('strtolower');
    }

    public function purifyHtml($htmlpurifier = null)
    {
        return $this->addRule(function ($value) use ($htmlpurifier) {
            if (!$htmlpurifier && function_exists('app')) {
                $htmlpurifier = app()->htmlpurifier;
            }

            if (!$htmlpurifier || !method_exists($htmlpurifier, 'purify')) {
                throw new \InvalidArgumentException('Invalid htmlpurifer!');
            }

            return $htmlpurifier->purify($value);
        });
    }

    public function filter($rule)
    {
        return $this->addRule($rule);
    }

    public function addRule($rule)
    {
        if (!is_callable($rule)) {
            throw new \InvalidArgumentException('A rule must be a callable! Invalid rule: '.json_encode($rule));
        }

        $this->rules[] = $rule;

        return $this;
    }

    public function sanitize($value)
    {
        if ($this->hasDefaultValue && ($value === self::undefinedValue() || empty($value))) {
            $value = $this->defaultValue;
        }

        if ($value === self::undefinedValue()
            || ($this->isNotBlank && $this->isItBlank($value))
            || ($this->isNotEmpty && empty($value))
            || ($this->isNotNull && is_null($value))
        ) {
            if ($this->isRequired) {
                throw new RequiredValueMissingException($this, $value);
            } else {
                return self::undefinedValue();
            }
        }

        $value = $this->beforeApplyRules($value);

        return $this->applyRules($value);
    }

    protected function beforeApplyRules($value)
    {
        return $value;
    }

    protected function applyRules($value)
    {
        foreach ($this->rules as $rule) {
            $value = call_user_func($rule, $value);
        }

        return $value;
    }

    public function __toString()
    {
        $constraints = [];
        if ($this->isRequired) {
            $constraints[] = 'required';
        } else {
            $constraints[] = 'optional';
        }

        if ($this->isNotNull) {
            $constraints[] = 'notNull';
        }

        if ($this->isNotEmpty) {
            $constraints[] = 'notEmpty';
        }

        if ($this->isNotBlank) {
            $constraints[] = 'notBlank';
        }

        if ($this->hasDefaultValue) {
            $constraints[] = 'default={'.var_export($this->defaultValue, true).'}';
        }

        if ($this->rules) {
            $constraints[] = count($this->rules).' rules';
        }

        return $this->type.'('.implode(', ', $constraints).')'.($this->desc ? ' // '.$this->desc : '');
    }
}
