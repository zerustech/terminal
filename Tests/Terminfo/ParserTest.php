<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace ZerusTech\Component\Terminal\Tests\Terminfo;

use ZerusTech\Component\Terminal\Terminfo\Parser;

/**
 * Test case for terminfo parser.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{
    protected $base;

    protected $path;

    protected $data;

    protected $parser;

    protected $terminfo;

    public function setup()
    {
        $this->base = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Fixtures';

        $this->path = $this->base.DIRECTORY_SEPARATOR.'xterm-256color';

        $this->data = file_get_contents($this->path);

        $this->parser = new Parser($this->data);

        $this->parser->parse();

        $this->terminfo = $this->parser->getTerminfo();
    }

    public function tearDown()
    {
        $this->base = null;

        $this->path = null;

        $this->data = null;

        $this->parser = null;

        $this->terminfo = null;
    }

    public function  testConstructor()
    {
        $parser = new Parser($this->data);

        $this->assertEquals(['raw-data' => $this->data], $parser->getTerminfo());

        $this->assertFalse($parser->isParsed());
    }

    /**
     * @depends testConstructor
     * @dataProvider littleEndianIntegerProvider
     */
    public function testLittleEndianInteger($expected, $firstByte, $secondByte)
    {
        $this->assertEquals($expected, $this->parser->parseLittleEndianInteger($firstByte, $secondByte));
    }

    public function littleEndianIntegerProvider()
    {
        return [
            [-1, "\xFF", "\xFF"],
            [0x00FF, "\xFF", "\x00"],
            [0xFF00, "\x00", "\xFF"],
            [0x1234, "\x34", "\x12"],
        ];
    }

    /**
     * @depends testLittleEndianInteger
     */
    public function testHeaders()
    {
        $data = [
            [0, 1, Parser::KEY_HEADER_MAGIC_NUMBER],
            [2, 3, Parser::KEY_HEADER_NAMES_BYTES],
            [4, 5, Parser::KEY_HEADER_BOOLEANS_BYTES],
            [6, 7, Parser::KEY_HEADER_NUMBERS_INTEGERS],
            [8, 9, Parser::KEY_HEADER_STRINGS_INTEGERS],
            [10, 11, Parser::KEY_HEADER_STRING_TABLE_BYTES],
        ];

        foreach ($data as $row) {

            list($firstByteIndex, $secondByteIndex, $key) = $row;

            $this->assertEquals(
                $this->parser->parseLittleEndianInteger($this->data[$firstByteIndex], $this->data[$secondByteIndex]),
                $this->terminfo[Parser::KEY_HEADERS][$key]
            );
        }
    }

    /**
     * @depends testHeaders
     */
    public function testOffsets()
    {
        $data = [];

        $offset = 0;
        $data[] = [$offset, Parser::KEY_HEADERS];

        $offset += 12;
        $data[] = [$offset, Parser::KEY_NAMES];

        $offset += $this->terminfo[Parser::KEY_HEADERS][Parser::KEY_HEADER_NAMES_BYTES];
        $data[] = [$offset, Parser::KEY_BOOLEANS];

        $offset += $this->terminfo[Parser::KEY_HEADERS][Parser::KEY_HEADER_BOOLEANS_BYTES];
        $offset = (1 === $offset % 2) ? $offset + 1 : $offset;
        $data[] = [$offset, Parser::KEY_NUMBERS];

        $offset += 2 * $this->terminfo[Parser::KEY_HEADERS][Parser::KEY_HEADER_NUMBERS_INTEGERS];
        $data[] = [$offset, Parser::KEY_STRINGS];

        $offset += 2 * $this->terminfo[Parser::KEY_HEADERS][Parser::KEY_HEADER_STRINGS_INTEGERS];
        $data[] = [$offset, Parser::KEY_STRING_TABLE];

        foreach ($data as $row) {

            list($offset, $key) = $row;

            $this->assertEquals($offset, $this->terminfo[Parser::KEY_OFFSETS][$key]);
        }
    }

    /**
     * @depends testOffsets
     */
    public function testNames()
    {
        $names = substr(
            $this->data,
            $this->terminfo[Parser::KEY_OFFSETS][Parser::KEY_NAMES],
            $this->terminfo[Parser::KEY_HEADERS][Parser::KEY_HEADER_NAMES_BYTES]
        );

        $this->assertEquals($names, $this->terminfo[Parser::KEY_NAMES]);
    }

    /**
     * @dpends testNames
     */
    public function testBooleans()
    {
        $booleans = substr(
            $this->data,
            $this->terminfo[Parser::KEY_OFFSETS][Parser::KEY_BOOLEANS],
            $this->terminfo[Parser::KEY_HEADERS][Parser::KEY_HEADER_BOOLEANS_BYTES]
        );

        for ($i = 0; $i < $this->terminfo[Parser::KEY_HEADERS][Parser::KEY_HEADER_BOOLEANS_BYTES]; $i++) {

            $expected = (1 === ord($booleans[$i])) ? true : false;

            $this->assertEquals($expected, $this->terminfo[Parser::KEY_BOOLEANS][$i]);
        }
    }

    /**
     * @dpends testBooleans
     */
    public function testNumbers()
    {
        $numbers = substr(
            $this->data,
            $this->terminfo[Parser::KEY_OFFSETS][Parser::KEY_NUMBERS],
            2 * $this->terminfo[Parser::KEY_HEADERS][Parser::KEY_HEADER_NUMBERS_INTEGERS]
        );

        for ($i = 0; $i < $this->terminfo[Parser::KEY_HEADERS][Parser::KEY_HEADER_NUMBERS_INTEGERS]; $i++) {

            $expected = $this->parser->parseLittleEndianInteger($numbers[2 * $i], $numbers[2 * $i + 1]);

            $this->assertEquals($expected, $this->terminfo[Parser::KEY_NUMBERS][$i]);
        }
    }

    /**
     * @depends testNumbers
     */
    public function testStringTable()
    {
        $expected = substr(
            $this->data,
            $this->terminfo[Parser::KEY_OFFSETS][Parser::KEY_STRING_TABLE],
            $this->terminfo[Parser::KEY_HEADERS][Parser::KEY_HEADER_STRING_TABLE_BYTES]
        );

        $this->assertEquals($expected, $this->terminfo[Parser::KEY_STRING_TABLE]);
    }

    /**
     * @depends testStringTable
     */
    public function testStrings()
    {
        $strings = substr(
            $this->data,
            $this->terminfo[Parser::KEY_OFFSETS][Parser::KEY_STRINGS],
            2 * $this->terminfo[Parser::KEY_HEADERS][Parser::KEY_HEADER_STRINGS_INTEGERS]
        );

        $stringTable = $this->terminfo[Parser::KEY_STRING_TABLE];

        for ($i = 0; $i < $this->terminfo[Parser::KEY_HEADERS][Parser::KEY_HEADER_STRINGS_INTEGERS]; $i++) {

            $offset = $this->parser->parseLittleEndianInteger($strings[2 * $i], $strings[2 * $i + 1]);

            $expected = null;

            if (-1 !== $offset && 65534 !== $offset) {

                $end = strpos($stringTable, "\x0", $offset);

                $end = (false === $end) ? strlen($stringTable) : $end;

                $length = $end - $offset;

                $expected = substr($stringTable, $offset, $length);
            }

            $this->assertEquals($expected, $this->terminfo[Parser::KEY_STRINGS][$i]);
        }
    }
}
