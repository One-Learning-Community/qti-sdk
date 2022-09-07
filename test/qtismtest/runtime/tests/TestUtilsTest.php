<?php

namespace qtismtest\runtime\tests;

use qtism\common\datatypes\QtiDatatype;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\data\state\AssociationValidityConstraint;
use qtism\data\state\ResponseValidityConstraint;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\tests\Utils as TestUtils;
use qtismtest\QtiSmTestCase;
use ReflectionClass;
use RuntimeException;

/**
 * Class TestUtilsTest
 */
class TestUtilsTest extends QtiSmTestCase
{
    /**
     * @dataProvider isResponseValidProvider
     * @param $expected
     * @param QtiDatatype|null $response
     * @param ResponseValidityConstraint $constraint
     */
    public function testIsResponseValid($expected, $response, ResponseValidityConstraint $constraint)
    {
        $this::assertEquals($expected, TestUtils::isResponseValid($response, $constraint));
    }

    /**
     * @return array
     */
    public function isResponseValidProvider()
    {
        $tests = [
            // Null values tests.
            [true, null, new ResponseValidityConstraint('RESPONSE', 0, 0)],
            [true, null, new ResponseValidityConstraint('RESPONSE', 0, 1)],
            [true, null, new ResponseValidityConstraint('RESPONSE', 0, 3)],
            [false, null, new ResponseValidityConstraint('RESPONSE', 1, 3)],
            [false, null, new ResponseValidityConstraint('RESPONSE', 2, 3)],
            [false, null, new ResponseValidityConstraint('RESPONSE', 1, 1)],
            [false, new QtiString(''), new ResponseValidityConstraint('RESPONSE', 1, 1)],
            [false, new MultipleContainer(BaseType::INTEGER), new ResponseValidityConstraint('RESPONSE', 1, 5)],
            [false, new OrderedContainer(BaseType::INTEGER), new ResponseValidityConstraint('RESPONSE', 1, 5)],
            [false, new RecordContainer(), new ResponseValidityConstraint('RESPONSE', 1, 1)],
            [true, new RecordContainer(['key' => new QtiInteger(1337)]), new ResponseValidityConstraint('RESPONSE', 1, 1)],

            // Single cardinality tests.
            [true, new QtiString('string!'), new ResponseValidityConstraint('RESPONSE', 1, 1)],
            [true, new QtiInteger(1337), new ResponseValidityConstraint('RESPONSE', 1, 0)],

            // Multiple cardinality tests.
            [true, new MultipleContainer(BaseType::INTEGER, [new QtiInteger(1337)]), new ResponseValidityConstraint('RESPONSE', 1, 1)],
            [true, new MultipleContainer(BaseType::INTEGER, [new QtiInteger(1337), new QtiInteger(1337)]), new ResponseValidityConstraint('RESPONSE', 1, 2)],
            [true, new OrderedContainer(BaseType::INTEGER, [new QtiInteger(1337), new QtiInteger(1337)]), new ResponseValidityConstraint('RESPONSE', 1, 2)],
            [false, new OrderedContainer(BaseType::INTEGER, [new QtiInteger(1337), new QtiInteger(1337)]), new ResponseValidityConstraint('RESPONSE', 1, 1)],
            [false, new OrderedContainer(BaseType::INTEGER, [new QtiInteger(1337), new QtiInteger(1337)]), new ResponseValidityConstraint('RESPONSE', 0, 1)],
            [true, new OrderedContainer(BaseType::INTEGER, [new QtiInteger(1337), new QtiInteger(1337)]), new ResponseValidityConstraint('RESPONSE', 0, 0)],
            [true, new RecordContainer(['key' => new QtiInteger(1337)]), new ResponseValidityConstraint('RESPONSE', 1, 1)],

            // PatternMask tests.
            [false, null, new ResponseValidityConstraint('RESPONSE', 1, 1, 'string')],
            [false, null, new ResponseValidityConstraint('RESPONSE', 1, 1, '/sd$[a-(')],
            [true, new QtiString('string'), new ResponseValidityConstraint('RESPONSE', 1, 1, 'string')],
            [false, new QtiString('strong'), new ResponseValidityConstraint('RESPONSE', 1, 1, 'string')],
            // PatternMask as maxlength tests - checking the length validation and also if unicode characters are processed with length of 1.
            [true, new QtiString('hi'), new ResponseValidityConstraint('RESPONSE', 1, 1, '^[\s\S]{0,2}$')],
            [false, new QtiString('hey'), new ResponseValidityConstraint('RESPONSE', 1, 1, '^[\s\S]{0,2}$')],
            [true, new QtiString('hå'), new ResponseValidityConstraint('RESPONSE', 1, 1, '^[\s\S]{0,2}$')],
            [false, new QtiString('håj'), new ResponseValidityConstraint('RESPONSE', 1, 1, '^[\s\S]{0,2}$')],
            [true, new QtiString('hé'), new ResponseValidityConstraint('RESPONSE', 1, 1, '^[\s\S]{0,2}$')],
            [true, new QtiString('hæ'), new ResponseValidityConstraint('RESPONSE', 1, 1, '^[\s\S]{0,2}$')],
            [true, new QtiString('h😊'), new ResponseValidityConstraint('RESPONSE', 1, 1, '^[\s\S]{0,2}$')],
            [true, new QtiString('ты'), new ResponseValidityConstraint('RESPONSE', 1, 1, '^[\s\S]{0,2}$')], // ru
            [true, new QtiString('じす'), new ResponseValidityConstraint('RESPONSE', 1, 1, '^[\s\S]{0,2}$')], // jp
            [true, new QtiString('谢谢'), new ResponseValidityConstraint('RESPONSE', 1, 1, '^[\s\S]{0,2}$')], // zh
            [true, new QtiString('قط'), new ResponseValidityConstraint('RESPONSE', 1, 1, '^[\s\S]{0,2}$')], // ar
            [true, new QtiString('אב'), new ResponseValidityConstraint('RESPONSE', 1, 1, '^[\s\S]{0,2}$')], // he

            [
                false,
                new QtiString('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex'),
                new ResponseValidityConstraint('RESPONSE', 1, 1, '^(?:(?:[^\s\:\!\?\;\…\€]+)[\s\:\!\?\;\…\€]*){0,30}$')
            ], // 33 words
            [
                true,
                new QtiString('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris'),
                new ResponseValidityConstraint('RESPONSE', 1, 1, '^(?:(?:[^\s\:\!\?\;\…\€]+)[\s\:\!\?\;\…\€]*){0,30}$')
            ], //29 words
            [
                false,
                new QtiString('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborumLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborumLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborumLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborumLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborumLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborumLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborumLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse'),
                new ResponseValidityConstraint('RESPONSE', 1, 1, '^(?:(?:[^\s\:\!\?\;\…\€]+)[\s\:\!\?\;\…\€]*){0,1000}$')
            ], // 1005 words
            [
                true,
                new QtiString('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborumLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborumLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborumLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborumLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborumLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborumLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborumLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.'),
                new ResponseValidityConstraint('RESPONSE', 1, 1, '^(?:(?:[^\s\:\!\?\;\…\€]+)[\s\:\!\?\;\…\€]*){0,1000}$')
            ], //995 words

            [true, new MultipleContainer(BaseType::STRING, [new QtiString('string'), new QtiString('string')]), new ResponseValidityConstraint('RESPONSE', 2, 2, 'string')],
            [false, new MultipleContainer(BaseType::STRING, [new QtiString('strong'), new QtiString('string')]), new ResponseValidityConstraint('RESPONSE', 2, 2, 'string')],
            [false, new MultipleContainer(BaseType::STRING, [new QtiString('string'), new QtiString('strong')]), new ResponseValidityConstraint('RESPONSE', 2, 2, 'string')],
            [false, new OrderedContainer(BaseType::STRING, [new QtiString('strong')]), new ResponseValidityConstraint('RESPONSE', 0, 1, 'string')],
            [true, new MultipleContainer(BaseType::STRING), new ResponseValidityConstraint('RESPONSE', 0, 1, 'string')],
            [true, new RecordContainer(), new ResponseValidityConstraint('RESPONSE', 0, 1, 'string')],
            [true, new RecordContainer(['key' => new QtiString('strong')]), new ResponseValidityConstraint('RESPONSE', 0, 1, 'string')],
            // -> Extreme edge-cases where the engine detects that the Record is in use with a stringInteraction (the 'stringValue' key is set).
            [false, new RecordContainer(['stringValue' => new QtiString('strong')]), new ResponseValidityConstraint('RESPONSE', 0, 1, 'string')],
            [false, new RecordContainer(['stringValue' => new QtiString('')]), new ResponseValidityConstraint('RESPONSE', 0, 1, 'string')],
            [true, new RecordContainer(['stringValue' => null]), new ResponseValidityConstraint('RESPONSE', 0, 1, 'string')],
        ];

        // Associations tests.
        $constraint1 = new ResponseValidityConstraint('RESPONSE', 1, 1);
        $constraint1->addAssociationValidityConstraint(new AssociationValidityConstraint('ID1', 1, 1));

        $tests[] = [true, new QtiPair('ID1', 'ID2'), $constraint1];
        $tests[] = [true, new MultipleContainer(BaseType::PAIR, [new QtiPair('ID1', 'ID2')]), $constraint1];
        $tests[] = [false, new MultipleContainer(BaseType::PAIR, [new QtiPair('ID1', 'ID2'), new QtiPair('ID1', 'ID3')]), $constraint1];
        $tests[] = [false, null, $constraint1];
        $tests[] = [false, new MultipleContainer(BaseType::PAIR), $constraint1];

        $tests[] = [true, new QtiDirectedPair('ID1', 'ID2'), $constraint1];
        $tests[] = [true, new MultipleContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('ID1', 'ID2')]), $constraint1];
        $tests[] = [false, new MultipleContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('ID1', 'ID2'), new QtiDirectedPair('ID1', 'ID3')]), $constraint1];

        $constraint2 = new ResponseValidityConstraint('RESPONSE', 1, 0);
        $constraint2->addAssociationValidityConstraint(new AssociationValidityConstraint('ID1', 2, 0));
        $tests[] = [false, null, $constraint2];
        $tests[] = [false, new QtiPair('ID1', 'ID2'), $constraint2];
        $tests[] = [true, new MultipleContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('ID1', 'ID1'), new QtiDirectedPair('ID1', 'ID1')]), $constraint2];

        $constraint3 = new ResponseValidityConstraint('RESPONSE', 1, 0);
        $constraint3->addAssociationValidityConstraint(new AssociationValidityConstraint('ID1', 4, 0));
        $tests[] = [true, new MultipleContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('ID1', 'ID1'), new QtiDirectedPair('ID1', 'ID1')]), $constraint3];

        return $tests;
    }

    public function testIsResponseValidRuntimeException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('PCRE Engine internal error');

        $valid = TestUtils::isResponseValid(
            new QtiString('checkme'),
            new ResponseValidityConstraint(
                'RESPONSE',
                1,
                1,
                '/abc[A-'
            )
        );
    }

    /**
     * @dataProvider provideMatchGroups
     */
    public function testIsSingleMatchGroup(bool $expected, string $pattern): void
    {
        $class = new ReflectionClass(TestUtils::class);
        $method = $class->getMethod('isSingleMatchGroup');
        $method->setAccessible(true);

        $result = $method->invoke(null, $pattern);

        self::assertEquals($expected, $result);
    }

    public function provideMatchGroups(): array
    {
        return [
            [true, '/^(?:(?:[^\s\:\!\?\;\…\€]+)[\s\:\!\?\;\…\€]*){0,30}$/'],
            [true, '/^test (?:(?:[^\s\:\!\?\;\…\€]+)[\s\:\!\?\;\…\€]*){0,30}$/'],
            [false, '/^(?:[^\s\:\!\?\;\…\€]+)(?:(?:[^\s\:\!\?\;\…\€]+)[\s\:\!\?\;\…\€]*){0,30}$/']
        ];
    }
}
