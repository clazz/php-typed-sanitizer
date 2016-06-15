PHP Typed Sanitizer (PHP类型净化器) [![](https://travis-ci.org/clazz/php-typed-sanitizer.svg)](https://travis-ci.org/clazz/php-typed-sanitizer)
===================
如何安装？
----------

推荐使用composer进行安装：

```sh
composer require clazz/typed-sanitizer
```

使用说明
--------

这个净化器可以用于净化外部输入（用户输入、接口输入）。 啥也不说了，上段代码试试：

```php

use Clazz\Typed\Types\Type;

// 假设有个接口想要一个用户的数据，而外部输入是这样：
$input = [
    'id' => '123',
    'name' => ' James William ',
    'age' => '12',
    'isMale' => '1',
];

// 可以定义我们想要的是这样的：
$userDefinition = Type::arr([
    'id'     => 'int',
    'name'   => Type::string()->trim()->length('< 30'),
    'age'    => Type::int()->isRequired(),
    'isMale' => Type::boolean(),
]);

// 然后就可以执行净化了：
$sanitizedUserData = $userDefinition->sanitize($input);

// 看看得到的数据，果然是预期的：
var_export($sanitizedUserData);
//输出:
//    array (
//      'id' => 123,
//      'name' => 'James William',
//      'age' => 12,
//      'isMale' => true,
//    )

```

为了方便使用，本库还提供了两个工具类： `Clazz\Typed\Sanitizer` 和 `Clazz\Typed\Illuminate\Support\Facades\Input`.

使用`Clazz\Typed\Sanitizer`可以省去定义`Type`，修改后的示例：

```php

use Clazz\Typed\Types\Type;
use Clazz\Typed\Sanitizer;

// 假设有个接口想要一个用户的数据，而外部输入是这样：
$input = [
    'id' => '123',
    'name' => ' James William ',
    'age' => '12',
    'isMale' => '1',
];

// 类型定义 + 执行净化了：
$sanitizedUserData = Sanitizer::getSanitized([
         'id'     => 'int',
         'name'   => Type::string()->trim()->length('< 30'),
         'age'    => Type::int()->isRequired(),
         'isMale' => Type::boolean(),
     ], $input);
```

在`Laravel`/`Lumen`中使用`Clazz\Typed\Illuminate\Support\Facades\Input`就更方便了 —— 它会默认从HTTP请求中获取输入：

```php

use Clazz\Typed\Types\Type;
use Clazz\Typed\Illuminate\Support\Facades\Input;

// 假设有个接口想要一个用户的数据，而HTTP的输入参数是这样：
// id=123&name=%20%20James%20William%20%20&age=12&isMale=1


// 类型定义 + 执行净化了：
$sanitizedUserData = Input::getSanitized([
         'id'     => 'int',
         'name'   => Type::string()->trim()->length('< 30'),
         'age'    => Type::int()->isRequired(),
         'isMale' => Type::boolean(),
     ]);
```