<?php

namespace Clazz\Typed;

use Clazz\Typed\Exceptions\EmptyValueException;
use Clazz\Typed\Exceptions\InvalidFormatException;
use Clazz\Typed\Exceptions\RequiredValueMissingException;
use Clazz\Typed\Exceptions\TypeMissMatchException;
use Clazz\Typed\Exceptions\ValueTooLongException;
use Clazz\Typed\Exceptions\ValueTooShortException;
use Clazz\Typed\Types\ArrayType;
use Clazz\Typed\Types\Type;

class SanitizerTest extends \PHPUnit_Framework_TestCase
{
    public function test_comment()
    {
        $comment = 'test一定一定要传一个整数！';

        try {
            Sanitizer::getSanitized([
                'test' => Type::int()->comment($comment),
            ], ['test' => 'hello']);
            $this->assertTrue(false);
        } catch (TypeMissMatchException $e) {
            $this->assertEquals($comment, $e->getMessage());
        }
    }

    public function test_desc()
    {
        $desc = '姓名';

        try {
            Sanitizer::getSanitized([
                'name' => Type::str()->notEmpty()->desc($desc),
            ], ['name' => null]);
            $this->assertTrue(false);
        } catch (TypeMissMatchException $e) {
            $this->assertContains($desc, $e->getMessage());
        }
    }

    public function test_phone()
    {
        $this->assertEquals('18688888888', Sanitizer::getSanitized('phone', Type::phone(), ['phone' => '18688888888']));

        $this->assertEquals('18688888888', Sanitizer::getSanitized('phone', Type::mobile(), ['phone' => '18688888888']));

        $this->assertEquals('18688888888', Sanitizer::getSanitized('phone', Type::mobilePhone(), ['phone' => '18688888888']));

        try {
            Sanitizer::getSanitized('phone', Type::phone(), ['phone' => '123456']);
        } catch (InvalidFormatException $e) {
            $this->assertContains('phone', $e->getMessage());
        }
    }

    public function test_undefined_values()
    {
        $this->assertTrue(Type::undefinedValue() === ArrayType::undefinedValue());
    }

    public function test_example_for_single_field()
    {
        $input = [
            'id' => '123',
            'name' => ' James William ',
            'age' => '12',
            'isMale' => '1',
        ];

        $this->assertSame(123, Sanitizer::getSanitized('id', Type::int(), $input));
        $this->assertSame('James William', Sanitizer::getSanitized('name', Type::string()->trim(), $input));
        $this->assertSame(12, Sanitizer::getSanitized('age', 'int', $input));
        $this->assertSame(true, Sanitizer::getSanitized('isMale', Type::bool(), $input));
        $this->assertSame(['Hello' => 'world'], Sanitizer::getSanitized('data', Type::any()->defaultValue(['Hello' => 'world']), $input));
    }

    public function test_example_for_array()
    {
        $input = [
            'id' => '123',
            'name' => ' James William ',
            'age' => '12',
            'isMale' => '1',
        ];

        $sanitized = Sanitizer::getSanitized([
            'id' => 'int',
            'name' => Type::string()->trim()->length('< 30'),
            'age' => Type::int()->isRequired(),
            'isMale' => Type::boolean(),
            'data' => Type::of('array')
                ->fields([
                    'Hello' => Type::string(),
                ])
                ->defaultValue([
                    'Hello' => 'world',
                ]),
        ], $input);

        $this->assertSame([
            'id' => 123,
            'name' => 'James William',
            'age' => 12,
            'isMale' => true,
            'data' => [
                'Hello' => 'world',
            ],
        ], $sanitized);
    }

    /**
     * @param $input
     * @param $definition
     * @param $expect
     * @dataProvider default_fields_dataProvider
     */
    public function test_default_fields($input, $definition, $expect)
    {
        $sanitized = Sanitizer::getSanitized($definition, $input);
        $this->assertSame($expect, $sanitized);
    }

    public function default_fields_dataProvider()
    {
        return [
            // 原始输入               定义                      净化后的输入
            [[], ['id' => Type::int()->defaultValue(123)], ['id' => 123]], // 数字
            [[], ['id' => Type::int()->defaultValue(123)->isOptional()], ['id' => 123]], // isOptional但是有默认值，则还是会返回对应的默认值（类似数据库的default机制）
            [['id' => ''], ['id' => Type::int()->defaultValue(123)], ['id' => 123]], // 如果是空字符串，则也是需要默认值的！
            [['id' => '0'], ['id' => Type::int()->defaultValue(123)], ['id' => 123]], // 如果是'0'，则也是需要默认值的！ -- 所有empty的值都是会覆盖为默认值
            [['id' => 0], ['id' => Type::int()->defaultValue(123)], ['id' => 123]], // 如果是0，则也是需要默认值的！ -- 所有empty的值都是会覆盖为默认值
            [['id' => false], ['id' => Type::int()->defaultValue(123)], ['id' => 123]], // 如果是false，则也是需要默认值的！ -- 所有empty的值都是会覆盖为默认值

            [[], ['name' => Type::string()->defaultValue('Unknown')], ['name' => 'Unknown']],
            [[], ['name' => Type::string()->defaultValue('Unknown')->isOptional()], ['name' => 'Unknown']],
            [['name' => ''], ['name' => Type::string()->defaultValue('Unknown')], ['name' => 'Unknown']],
        ];
    }

    /**
     * @param $input
     * @param $definition
     * @param $expect
     * @dataProvider optional_fields_dataProvider
     */
    public function test_optional_fields($input, $definition, $expect)
    {
        $sanitized = Sanitizer::getSanitized($definition, $input);
        $this->assertSame($expect, $sanitized);
    }

    public function optional_fields_dataProvider()
    {
        return [
            // 原始输入               定义                      净化后的输入
            [[], ['id' => Type::int()->isOptional()], []], // 数字
            [[], ['name' => Type::string()->isOptional()], []],

            [[], ['name' => Type::string()->defaultValue('Unknown')->isOptional()], ['name' => 'Unknown']], // isOptional但是有默认值，则还是会返回对应的默认值（类似数据库的default机制）

            // 空的字符串也认定为是没有值的！
            [['id' => ''], ['id' => Type::int()->isNotBlank()->isOptional()], []],
            [['name' => ''], ['name' => Type::string()->isNotBlank()->isOptional()], []],
        ];
    }

    public function test_valid_datetime_field()
    {
        $this->assertSame('2009-12-13 14:15:16', Sanitizer::getSanitized('test', Type::datetime(), [
            'test' => '2009-12-13 14:15:16',
        ]));
    }

    public function test_invalid_datetime_field()
    {
        $this->setExpectedException(InvalidFormatException::class);

        $this->assertSame('2009-12-13 14:15:16', Sanitizer::getSanitized('test', Type::datetime(), [
            'test' => '2009-12-13 asdsdkewlk',
        ]));
    }

    public function test_valid_date_field()
    {
        $this->assertSame('2009-12-13', Sanitizer::getSanitized('test', Type::date(), [
            'test' => '2009-12-13',
        ]));
    }

    public function test_invalid_date_field()
    {
        $this->setExpectedException(InvalidFormatException::class);

        $this->assertSame('2009-12-13 14:15:16', Sanitizer::getSanitized('test', Type::date(), [
            'test' => '200asdsdkewlk',
        ]));
    }

    public function test_valid_time_field()
    {
        $this->assertSame('14:15:16', Sanitizer::getSanitized('test', Type::time(), [
            'test' => '14:15:16',
        ]));
    }

    public function test_invalid_time_field()
    {
        $this->setExpectedException(InvalidFormatException::class);
        $this->assertSame('14:15:16', Sanitizer::getSanitized('test', Type::time(), [
            'test' => 'asdsdkewlk',
        ]));
    }

    /**
     * @param $input
     * @param $definition
     * @param $expected
     * @param $exception
     * @dataProvider empty_blanks_dataProvider
     */
    public function test_empty_blanks($input, $definition, $expected, $exception)
    {
        if (!is_null($exception)) {
            $this->setExpectedException($exception);
        }

        $field = 'test';
        $sanitized = Sanitizer::getSanitized($field, $definition, [$field => $input]);
        if (!is_null($exception)) {
            $this->assertTrue(false, 'There should be an exception: '.$exception);
        } else {
            $this->assertSame($expected, $sanitized);
        }
    }

    public function empty_blanks_dataProvider()
    {
        return [
            // input               definition                       expect                  exception
            ['0', Type::string()->isNotEmpty(), null, RequiredValueMissingException::class],
            [null, Type::string()->isNotEmpty(), null, RequiredValueMissingException::class],
            [false, Type::string()->isNotEmpty(), null, RequiredValueMissingException::class],
            ['false', Type::string()->isNotEmpty(), 'false', null],
            ['', Type::string()->isNotEmpty(), null, RequiredValueMissingException::class],
            ["\t", Type::string()->isNotEmpty(), "\t", null],
            ['  ', Type::string()->isNotEmpty(), '  ', null],
            ['0.0', Type::string()->isNotEmpty(), '0.0', null],
            [' abc ', Type::string()->isNotEmpty(), ' abc ', null],

            ['0', Type::string()->trim()->isNotEmpty(), null, RequiredValueMissingException::class],
            [null, Type::string()->trim()->isNotEmpty(), null, RequiredValueMissingException::class],
            [false, Type::string()->trim()->isNotEmpty(), null, RequiredValueMissingException::class],
            ['false', Type::string()->trim()->isNotEmpty(), 'false', null],
            ['', Type::string()->trim()->isNotEmpty(), null, RequiredValueMissingException::class],
            ["\t", Type::string()->trim()->isNotEmpty(), null, EmptyValueException::class],
            ['  ', Type::string()->trim()->isNotEmpty(), null, EmptyValueException::class],
            ['0.0', Type::string()->trim()->isNotEmpty(), '0.0', null],
            [' abc ', Type::string()->trim()->isNotEmpty(), 'abc', null],

            ['0', Type::string()->isNotBlank(), '0', null],
            [null, Type::string()->isNotBlank(), null, RequiredValueMissingException::class],
            [false, Type::string()->isNotBlank(), null, RequiredValueMissingException::class],
            ['false', Type::string()->isNotBlank(), 'false', null],
            ['', Type::string()->isNotBlank(), null, RequiredValueMissingException::class],
            ["\t", Type::string()->isNotBlank(), null, RequiredValueMissingException::class],
            ['  ', Type::string()->isNotBlank(), '  ', RequiredValueMissingException::class],
            ['0.0', Type::string()->isNotBlank(), '0.0', null],
            [' abc ', Type::string()->isNotBlank(), ' abc ', null],
        ];
    }

    public function test_str_operations()
    {
        $this->assertSame('abc', Sanitizer::getSanitized('test', 'str', ['test' => 'abc']));
        $this->assertSame('Abc', Sanitizer::getSanitized('test', Type::string()->ucfirst(), ['test' => 'abc']));
        $this->assertSame('abc', Sanitizer::getSanitized('test', Type::string()->lcfirst(), ['test' => 'abc']));

        $this->assertSame(['a', 'b', 'c'], Sanitizer::getSanitized('test', Type::string()->explode(','), ['test' => 'a,b,c']));

        $this->assertSame('a|b|c', Sanitizer::getSanitized('test', Type::any()->implode('|'), ['test' => ['a', 'b', 'c']]));
    }

    /**
     * @param $input
     * @param $definition
     * @param $expected
     * @param $exception
     * @dataProvider length_dataProvider
     */
    public function test_length($input, $definition, $expected, $exception)
    {
        if (!is_null($exception)) {
            $this->setExpectedException($exception);
        }

        $field = 'test_field';

        $sanitized = Sanitizer::getSanitized($field, $definition, [$field => $input]);
        $this->assertSame($expected, $sanitized);
    }

    public function length_dataProvider()
    {
        return [
            // input               definition                       expect                  exception

            // 正常情况
            [str_repeat('a', 10), Type::string()->length(10, 20), str_repeat('a', 10), null],
            [str_repeat('a', 15), Type::string()->length(10, 20), str_repeat('a', 15), null],
            [str_repeat('a', 20), Type::string()->length(10, 20), str_repeat('a', 20), null],
            [str_repeat('a', 10), Type::string()->length('10~20'), str_repeat('a', 10), null],
            [str_repeat('a', 15), Type::string()->length('10~20'), str_repeat('a', 15), null],
            [str_repeat('a', 20), Type::string()->length('10~20'), str_repeat('a', 20), null],
            [str_repeat('a', 10), Type::string()->length('10 ~ 20'), str_repeat('a', 10), null],
            [str_repeat('a', 15), Type::string()->length('10 ~ 20'), str_repeat('a', 15), null],
            [str_repeat('a', 20), Type::string()->length('10 ~ 20'), str_repeat('a', 20), null],
            [str_repeat('a', 10), Type::string()->length('10-20'), str_repeat('a', 10), null],
            [str_repeat('a', 15), Type::string()->length('10-20'), str_repeat('a', 15), null],
            [str_repeat('a', 20), Type::string()->length('10-20'), str_repeat('a', 20), null],
            [str_repeat('a', 10), Type::string()->length('10 - 20'), str_repeat('a', 10), null],
            [str_repeat('a', 15), Type::string()->length('10 - 20'), str_repeat('a', 15), null],
            [str_repeat('a', 20), Type::string()->length('10 - 20'), str_repeat('a', 20), null],
            [str_repeat('a', 10), Type::string()->length('10-20'), str_repeat('a', 10), null],
            [str_repeat('a', 15), Type::string()->length('10-20'), str_repeat('a', 15), null],
            [str_repeat('a', 20), Type::string()->length('10-20'), str_repeat('a', 20), null],
            [str_repeat('a', 10), Type::string()->length(20), str_repeat('a', 10), null],
            [str_repeat('a', 15), Type::string()->length(20), str_repeat('a', 15), null],
            [str_repeat('a', 20), Type::string()->length(20), str_repeat('a', 20), null],
            [str_repeat('a', 10), Type::string()->length('<= 20'), str_repeat('a', 10), null],
            [str_repeat('a', 15), Type::string()->length('<= 20'), str_repeat('a', 15), null],
            [str_repeat('a', 20), Type::string()->length('<= 20'), str_repeat('a', 20), null],
            [str_repeat('a', 10), Type::string()->length('>= 10'), str_repeat('a', 10), null],
            [str_repeat('a', 15), Type::string()->length('>= 10'), str_repeat('a', 15), null],
            [str_repeat('a', 20), Type::string()->length('>= 10'), str_repeat('a', 20), null],

            // 非trim
            [str_repeat(' ', 15), Type::string()->length('10~20'), str_repeat(' ', 15), null],

            // trim -- 之前和之后是有区别的！
            ['  '.str_repeat('a', 20).'  ', Type::string()->trim()->length('10~20'), str_repeat('a', 20), null],
            ['  '.str_repeat('a', 20).'  ', Type::string()->length('10~20')->trim(), null, ValueTooLongException::class],

            // 长度太短
            [str_repeat(' ', 5), Type::string()->length(10, 20), null, ValueTooShortException::class],
            [str_repeat(' ', 5), Type::string()->length('10~20'), null, ValueTooShortException::class],
            [str_repeat(' ', 5), Type::string()->length('10-20'), null, ValueTooShortException::class],
            [str_repeat(' ', 5), Type::string()->length('> 10'), null, ValueTooShortException::class],
            [str_repeat(' ', 5), Type::string()->length('>= 10'), null, ValueTooShortException::class],
            // 长度太长
            [str_repeat(' ', 25), Type::string()->length(10, 20), null, ValueTooLongException::class],
            [str_repeat(' ', 25), Type::string()->length('10~20'), null, ValueTooLongException::class],
            [str_repeat(' ', 25), Type::string()->length('10-20'), null, ValueTooLongException::class],
            [str_repeat(' ', 25), Type::string()->length('< 20'), null, ValueTooLongException::class],
            [str_repeat(' ', 25), Type::string()->length('<= 20'), null, ValueTooLongException::class],
        ];
    }

    /**
     * @param $input
     * @param $definition
     * @param $expect
     * @dataProvider multi_dataProvider
     */
    public function test_multi($input, $definition, $expect)
    {
        $sanitized = Sanitizer::getSanitized($definition, $input);
        $this->assertSame($expect, $sanitized);
    }

    public function multi_dataProvider()
    {
        return [
            ////////////////////////////////////////////////////////////////////////
            [
                // input:
                [
                    'id' => '123',
                    'name' => ' James William ',
                    'age' => '12',
                    'isMale' => '1',
                ],
                // definition:
                [
                    'id' => 'int',
                    'name' => Type::string()->length('< 30')->trim(),
                    'age' => Type::int()->isRequired(),
                    'isMale' => Type::boolean(),
                    'data' => Type::of('any')->defaultValue([
                        'Hello' => 'world',
                    ]),
                ],
                // expect:
                [
                    'id' => 123,
                    'name' => 'James William',
                    'age' => 12,
                    'isMale' => true,
                    'data' => [
                        'Hello' => 'world',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $input
     * @param $field
     * @param $definition
     * @param $expect
     * @dataProvider single_dataProvider
     */
    public function test_single($input, $field, $definition, $expect)
    {
        $sanitized = Sanitizer::getSanitized($field, $definition, $input);
        $this->assertSame($expect, $sanitized);
    }

    public function single_dataProvider()
    {
        $multiData = $this->multi_dataProvider();

        $singleData = [];

        foreach ($multiData as $item) {
            list($input, $definition, $expect) = $item;
            foreach ($definition as $field => $fieldDef) {
                $singleData[] = [$input, $field, $fieldDef, $expect[$field]];
            }
        }

        return $singleData;
    }

    public function test_CommaSeparatedArrayListOfType()
    {
        $this->assertEquals([1,2,3], Sanitizer::getSanitized('test', Type::commaSeparatedArrayListOf('int'), ['test' => '1,2,3']));
        $this->assertEquals([1], Sanitizer::getSanitized('test', Type::commaSeparatedArrayListOf('int'), ['test' => '1']));
        $this->assertEquals([], Sanitizer::getSanitized('test', Type::commaSeparatedArrayListOf('int'), ['test' => '']));
        $this->assertEquals([], Sanitizer::getSanitized('test', Type::commaSeparatedArrayListOf('int'), ['test' => null]));
    }
}
