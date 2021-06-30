<?php

namespace alcamo\file;

use PHPUnit\Framework\TestCase;

class InputStreamLineIteratorTest extends TestCase
{
    /**
     * @dataProvider iterateProvider
     */
    public function testIterate($text, $flags, $expectedContent)
    {
        $handle = fopen('php://temp', 'rw');

        fwrite($handle, $text);

        fseek($handle, 0);

        $iterator = new InputStreamLineIterator($handle, $flags);

        $this->assertSame((int)$flags, $iterator->getFlags());

        $content = [];

        foreach ($iterator as $key => $value) {
            $content[$key] = $value;
        }

        $this->assertSame($expectedContent, $content);
    }

    public function iterateProvider()
    {
        $text = <<<EOD
Lorem ipsum
dolor sit amet,
consetetur sadipscing elitr

sed
diam nonumy
eirmod tempor invidunt


EOD;

        return [
            'defaults' => [
                $text,
                null,
                [
                    1 => 'Lorem ipsum',
                    2 => 'dolor sit amet,',
                    3 => 'consetetur sadipscing elitr',
                    4 => '',
                    5 => 'sed',
                    6 => 'diam nonumy',
                    7 => 'eirmod tempor invidunt',
                    8 => ''
                ]
            ],
            'include-delimiter' => [
                $text,
                InputStreamLineIterator::INCLUDE_LINE_DELIMITER,
                [
                    1 => 'Lorem ipsum' . PHP_EOL,
                    2 => 'dolor sit amet,' . PHP_EOL,
                    3 => 'consetetur sadipscing elitr' . PHP_EOL,
                    4 => PHP_EOL,
                    5 => 'sed' . PHP_EOL,
                    6 => 'diam nonumy' . PHP_EOL,
                    7 => 'eirmod tempor invidunt' . PHP_EOL,
                    8 => PHP_EOL
                ]
            ],
            'skip_empty' => [
                $text,
                InputStreamLineIterator::SKIP_EMPTY,
                [
                    1 => 'Lorem ipsum',
                    2 => 'dolor sit amet,',
                    3 => 'consetetur sadipscing elitr',
                    4 => 'sed',
                    5 => 'diam nonumy',
                    6 => 'eirmod tempor invidunt'
                ]
            ],
            'include-delimiter-skip-empty' => [
                $text,
                InputStreamLineIterator::INCLUDE_LINE_DELIMITER
                | InputStreamLineIterator::SKIP_EMPTY,
                [
                    1 => 'Lorem ipsum' . PHP_EOL,
                    2 => 'dolor sit amet,' . PHP_EOL,
                    3 => 'consetetur sadipscing elitr' . PHP_EOL,
                    4 => 'sed' . PHP_EOL,
                    5 => 'diam nonumy' . PHP_EOL,
                    6 => 'eirmod tempor invidunt' . PHP_EOL
                ]
            ]
        ];
    }
}
