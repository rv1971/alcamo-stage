<?php

namespace alcamo\integer;

use PHPUnit\Framework\TestCase;
use alcamo\exception\{OutOfRange, SyntaxError};

class NonNegativeRangeTest extends TestCase
{
    /**
     * @dataProvider newFromStringProvider
     */
    public function testNewFromString(
        $str,
        $expectedMin,
        $expectedMax,
        $expectedString,
        $expectedIsDefined,
        $expectedIsExact
    ) {
        $range = NonNegativeRange::newFromString($str);

        $this->assertEquals(
            new NonNegativeRange($expectedMin, $expectedMax),
            $range
        );

        $this->assertSame($expectedMin, $range->getMin());

        $this->assertSame($expectedMax, $range->getMax());

        $this->assertSame($expectedString, (string)$range);

        $this->assertSame($expectedIsDefined, $range->isDefined());

        $this->assertSame($expectedIsExact, $range->isExact());
    }

    public function newFromStringProvider()
    {
        return [
            'empty' => [ '', 0, null, '', false, false ],
            'exact' => [ "  42\r\n", 42, 42, '42', true, true ],
            'left'  => [ '5 -', 5, null, '5-', true, false ],
            'right' => [ '0  -  99', 0, 99, '0-99', true, false ],
            'both'  => [ "7\t-12", 7, 12, '7-12', true, false ]
        ];
    }

    public function testNewFromStringException()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            'Syntax error in "45+"; not a valid length range'
        );

        NonNegativeRange::newFromString('45+');
    }

    public function testConstructException1()
    {
        $this->expectException(OutOfRange::class);
        $this->expectExceptionMessage(
            'Value "-1" out of range [0, ∞['
        );

        new NonNegativeRange(-1);
    }

    public function testConstructException2()
    {
        $this->expectException(OutOfRange::class);
        $this->expectExceptionMessage(
            'Value "2" out of range [3, ∞['
        );

        new NonNegativeRange(3, 2);
    }

    /**
     * @dataProvider containsProvider
     */
    public function testContains($range, $value, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            NonNegativeRange::newFromString($range)->contains($value)
        );
    }

    public function containsProvider()
    {
        return [
            'empty'   => [ '', 1, true ],
            'exact-1' => [ '77', 76, false ],
            'exact-2' => [ '77', 77, true ],
            'exact-3' => [ '77', 78, false ],
            'left-1'  => [ '5-', 4, false ],
            'left-2'  => [ '5-', 5, true ],
            'left-3'  => [ '5-', 6, true ],
            'right-1' => [ '0-9', 0, true ],
            'right-2' => [ '0-9', 9, true ],
            'right-3' => [ '0-9', 10, false ],
            'both-1'  => [ '20-30', 19, false ],
            'both-2'  => [ '20-30', 20, true ],
            'both-3'  => [ '20-30', 30, true ],
            'both-4'  => [ '20-30', 31, false ]
        ];
    }
}
