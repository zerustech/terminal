<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace ZerusTech\Component\Terminal\Terminfo;

/**
 * The Terminfo Parser class parses compiled terminfo file into structured data.
 *
 * The parsed data contains capability values in the standard order, so the
 * capability names are not needed.
 *
 * The terminfo file consists of headers, names, booleans, numbers,
 * strings and string table sections as follows:
 *
 * Headers:
 * - 12 bytes long
 * - Represents 6 little-endian short integers
 *   (the value represented is: ``second byte << 8 | first byte`` ).
 * - These integers are:
 *   1. the magic number: Octal 0432
 *   2. the size, in bytes, of the names section.
 *   3. the number of bytes in the booleans section.
 *   4. the number of short integers in the numbers section.
 *   5. the number of offsets (short integers) in the strings section.
 *   6. the size, in bytes, of the string table.
 *
 * Names section:
 * - Follows headers section.
 * - It contains the first line of the terminfo description.
 * - This section is terminated with an ASCII NUL character (\x00)
 * - But because the length, in bytes, of the string is defined in headers
 *   section, the NUL character is not used for determining the end of the
 *   string.
 *
 * Booleans section:
 * - This section contains one byte for each flag. The byte can be either 0 or 1.
 * - The boolean flags are in the same order as the ncurses file: include/Caps.
 * - This section does not need contain all boolean flags and absent flags are
 *   be intepreted as false.
 *
 * Boundary byte:
 * - Between the booleans section and the numbers section, a boundary byte might
 *   be inserted to ensure the numbers section begins on an even byte.
 *
 * Numbers section:
 * - The numbers section contains one little-endian short integer for each
 *   number capability.
 * - The number capabilities are in the same order as the ncurses file:
 *   include/Caps.
 * - If the value presented is -1 ``{0xff, 0xff}``, the capability is taken to be
 *   missing.
 *
 * Strings section:
 * - The strings section contains one little-endian short integer for each
 *   string capability.
 * - A value of -1 means the capability is missing.
 * - The short integer value is taken as an offset from the beginning of the
 *   string table section.
 * - Due to a bug in xterm-256color terminal, ``{0xfe, 0xff}`` should also be
 *   treated as -1
 *
 * String table section:
 * - This is the final section. It contains all the values of string
 *   capabilities referenced in the strings section.
 * - Each string is null terminated.
 *
 * Resources:
 * - http://linux.die.net/man/5/term
 * - http://github.com/hoaproject/Console/blob/master/Tput.php
 * - https://github.com/prodigeni/blessed/blob/master/lib/tput.js
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class Parser
{
    /**
     * Key of the raw data element in terminfo array.
     */
    const KEY_RAW_DATA = 'raw-data';

    /**
     * Key of the headers section in terminfo array.
     */
    const KEY_HEADERS = 'headers';

    /**
     * Key of the offsets section in terminfo array.
     */
    const KEY_OFFSETS = 'offsets';

    /**
     * Key of the names section in terminfo array.
     */
    const KEY_NAMES = 'names';

    /**
     * Key of the booleans section in terminfo array.
     */
    const KEY_BOOLEANS = 'booleans';

    /**
     * Key of the numbers section in terminfo array.
     */
    const KEY_NUMBERS = 'numbers';

    /**
     * Key of the strings section in terminfo array.
     */
    const KEY_STRINGS = 'strings';

    /**
     * Key of the string table section in terminfo array.
     */
    const KEY_STRING_TABLE = 'string-table';

    /**
     * Key of the magic number in headers array.
     */
    const KEY_HEADER_MAGIC_NUMBER = 'magic-number';

    /**
     * Key of 'the size of names section' in headers array.
     */
    const KEY_HEADER_NAMES_BYTES = 'names-bytes';

    /**
     * Key of 'the number of bytes in booleans section' in headers array.
     */
    const KEY_HEADER_BOOLEANS_BYTES = 'booleans-bytes';

    /**
     * Key of 'the number of short-integers in numbers section' in headers array.
     */
    const KEY_HEADER_NUMBERS_INTEGERS = 'numbers-integers';

    /**
     * Key of 'the number of short-integers in strings section' in headers array.
     */
    const KEY_HEADER_STRINGS_INTEGERS = 'strings-integers';

    /**
     * Key of 'the size of string table section' in headers array.
     */
    const KEY_HEADER_STRING_TABLE_BYTES = 'string-table-bytes';

    /**
     * The passed terminfo.
     *
     * The data structure of the passed data is as follows:
     *
     *     [
     *         'raw-data' => ..., // the raw data
     *         'headers' => [
     *             'magic-number' => 0432,
     *             'names-bytes' => ...,
     *             'booleans-bytes' => ...,
     *             'numbers-integers' => ...,
     *             'string-integers' => ...,
     *             'string-table-bytes' => ...
     *         ],
     *         'offsets' => [
     *             'headers' => 0,
     *             'names' => 12,
     *             'booleans' => ...,
     *             'numbers' => ...,
     *             'strings' => ...,
     *             'string-table' => ...
     *         ],
     *         'names' => ...,
     *         'booleans' => [...],
     *         'numbers' => [...],
     *         'strings' => [...]
     *     ]
     *
     * @var array
     */
    private $terminfo = null;

    /**
     * @var bool Flag indicates if the compiled data has been parsed.
     */
    private $parsed = false;

    /**
     * Constructor.
     *
     * @param string $data The compiled terminfo data.
     *
     */
    public function __construct($data)
    {
        $this->terminfo = ['raw-data' => $data];

        $this->parsed = false;
    }

    /**
     * Returns the parsed terminfo data.
     *
     * @return array The parsed data.
     */
    public function getTerminfo()
    {
        return $this->terminfo;
    }

    /**
     * Checks if the compiled data has been parsed.
     *
     * @return bool True if it has been parsed, and false otherwise.
     */
    public function isParsed()
    {
        return $this->parsed;
    }

    /**
     * Parses the compiled terminfo data.
     *
     * @return Parser Current instance.
     */
    public function parse()
    {
        if (!$this->parsed) {

            $this
                ->parseHeaders()
                ->parseOffsets()
                ->parseNames()
                ->parseBooleans()
                ->parseNumbers()
                ->parseStringTable()
                ->parseStrings();

            $this->parsed = true;
        }

        return $this;
    }

    /**
     * Parses two bytes in little-endian order into one short integer.
     *
     * @param int $firstByte The first byte.
     * @param int $secondByte The second byte.
     * @return int The parsed short integer, or -1 if both bytes are 0xFF.
     */
    public function parseLittleEndianInteger($firstByte, $secondByte)
    {
        $value = 0;

        $lowByte = ord($firstByte);

        $highByte = ord($secondByte);

        // If both bytes are 0xFF, the value will be intepreted as -1.
        if (255 == $lowByte && 255 == $highByte) {

            $value = -1;

        } else {

            $value = $highByte << 8 | $lowByte;
        }

        return $value;
    }

    /**
     * Parses headers section from the compiled terminfo data.
     *
     * The passed headers data is stored in ``$this->terminfo['headers']``
     *
     * @return Parser Current instance.
     */
    private function parseHeaders()
    {
        $data = $this->terminfo[self::KEY_RAW_DATA];

        $headers = [
            // magic number: 0432
            self::KEY_HEADER_MAGIC_NUMBER => $this->parseLittleEndianInteger($data[0], $data[1]),

            // the size, in bytes, of the names section.
            self::KEY_HEADER_NAMES_BYTES => $this->parseLittleEndianInteger($data[2], $data[3]),

            // the number of bytes in the booleans section.
            self::KEY_HEADER_BOOLEANS_BYTES => $this->parseLittleEndianInteger($data[4], $data[5]),

            // the number of short integers in the numbers section.
            self::KEY_HEADER_NUMBERS_INTEGERS => $this->parseLittleEndianInteger($data[6], $data[7]),

            // the number of short integers in the strings section.
            self::KEY_HEADER_STRINGS_INTEGERS => $this->parseLittleEndianInteger($data[8], $data[9]),

            // the size, in bytes, of the string table section.
            self::KEY_HEADER_STRING_TABLE_BYTES => $this->parseLittleEndianInteger($data[10], $data[11]),
        ];

        $this->terminfo[self::KEY_HEADERS] = $headers;

        return $this;
    }

    /**
     * Parses the section offsets.
     *
     * Parses the section offsets, from the compiled terminfo and stores them in
     * ``$this->terminfo['offsets']``. The offset of each section is an offset,
     * in byte, from the beginning of the data to the beginning of the section.
     *
     * @return Parser Current instance.
     */
    private function parseOffsets()
    {
        $headers = $this->terminfo[self::KEY_HEADERS];

        $offsets = [];

        // Terminfo begins with headers, thus, its offset is 0.
        $offsets[self::KEY_HEADERS] = 0;

        // Names section follows headers
        $offsets[self::KEY_NAMES] = count($headers) * 2;

        // Booleans section follows names section.
        $offsets[self::KEY_BOOLEANS] = $offsets[self::KEY_NAMES] + $headers[self::KEY_HEADER_NAMES_BYTES];

        // Numbers section follows boolean section.
        $offsets[self::KEY_NUMBERS] = $offsets[self::KEY_BOOLEANS] + $headers[self::KEY_HEADER_BOOLEANS_BYTES];

        // Inserts a boundary byte, if numbers section begins on an odd byte.
        if (1 === $offsets[self::KEY_NUMBERS] % 2) {

            $offsets[self::KEY_NUMBERS] += 1;
        }

        // Strings section follows numbers section.
        $offsets[self::KEY_STRINGS] = $offsets[self::KEY_NUMBERS] + $headers[self::KEY_HEADER_NUMBERS_INTEGERS] * 2;

        // String table follows strings section.
        $offsets[self::KEY_STRING_TABLE] = $offsets[self::KEY_STRINGS] + $headers[self::KEY_HEADER_STRINGS_INTEGERS] * 2;

        $this->terminfo[self::KEY_OFFSETS] = $offsets;

        return $this;
    }

    /**
     * Parses names section.
     *
     * Parses names from the terminfo and stores them in
     * ``$this->terminfo['names']``
     *
     * @return Parser Current instance.
     */
    private function parseNames()
    {
        $data = $this->terminfo[self::KEY_RAW_DATA];

        $offsets = $this->terminfo[self::KEY_OFFSETS];

        $headers = $this->terminfo[self::KEY_HEADERS];

        $names = substr($data, $offsets[self::KEY_NAMES], $headers[self::KEY_HEADER_NAMES_BYTES]);

        $this->terminfo[self::KEY_NAMES] = $names;

        return $this;
    }

    /**
     * Parses boolean capabilities.
     *
     * Passes boolean capabilities from the terminfo and stores them in
     * ``$this->terminfo['booleans']``
     *
     * @return Parser Current instance.
     */
    private function parseBooleans()
    {
        $data = $this->terminfo[self::KEY_RAW_DATA];

        $offsets = $this->terminfo[self::KEY_OFFSETS];

        $headers = $this->terminfo[self::KEY_HEADERS];

        $offset = $offsets[self::KEY_BOOLEANS];

        $booleans = [];

        for ($i = 0; $i < $headers[self::KEY_HEADER_BOOLEANS_BYTES]; $i++) {

            $booleans[] = (1 === ord($data[$offset + $i]));
        }

        $this->terminfo[self::KEY_BOOLEANS] = $booleans;

        return $this;
    }

    /**
     * Parses number capabilities.
     *
     * Parses number capabilities from the terminfo and stores them in
     * ``$this->terminfo['numbers']``
     *
     * @return Parser Current instance.
     */
    private function parseNumbers()
    {
        $data = $this->terminfo[self::KEY_RAW_DATA];

        $offsets = $this->terminfo[self::KEY_OFFSETS];

        $headers = $this->terminfo[self::KEY_HEADERS];

        $offset = $offsets[self::KEY_NUMBERS];

        $numbers = [];

        for ($i = 0; $i < $headers[self::KEY_HEADER_NUMBERS_INTEGERS]; $i++) {

            $firstByte = $data[$offset + 2 * $i];

            $secondByte = $data[$offset + 2 * $i + 1];

            $number = $this->parseLittleEndianInteger($firstByte, $secondByte);

            $numbers[] = $number;
        }

        $this->terminfo[self::KEY_NUMBERS] = $numbers;

        return $this;
    }

    /**
     * Parses string table.
     *
     * Parses string table from the terminfo and stores them in
     * ``$this->terminfo['string-table']``
     *
     * @return Parser Current instance.
     */
    private function parseStringTable()
    {
        $data = $this->terminfo[self::KEY_RAW_DATA];

        $offsets = $this->terminfo[self::KEY_OFFSETS];

        $headers = $this->terminfo[self::KEY_HEADERS];

        $offset = $offsets[self::KEY_STRING_TABLE];

        $this->terminfo[self::KEY_STRING_TABLE] = substr($data, $offset, $headers[self::KEY_HEADER_STRING_TABLE_BYTES]);

        return $this;
    }

    /**
     * Parses string capabilities.
     *
     * Parses string capabilities from the terminfo and stores them in
     * ``$this->terminfo['strings']``
     *
     * @return Parser Current instance.
     */
    private function parseStrings()
    {
        $data = $this->terminfo[self::KEY_RAW_DATA];

        $offsets = $this->terminfo[self::KEY_OFFSETS];

        $headers = $this->terminfo[self::KEY_HEADERS];

        $offset = $offsets[self::KEY_STRINGS];

        $strings = [];

        for ($i = 0; $i < $headers[self::KEY_HEADER_STRINGS_INTEGERS]; $i++) {

            $firstByte = $data[$offset + 2 * $i];

            $secondByte = $data[$offset + 2 * $i + 1];

            $stringOffset = $this->parseLittleEndianInteger($firstByte, $secondByte);

            $strings[] = $this->parseStringFromStringTable($stringOffset);
        }

        $this->terminfo[self::KEY_STRINGS] = $strings;
    }

    /**
     * Parses a single string capability at the given offset from the string
     * table.
     *
     * @param int $offset The offset, from the beginning of the string table, where the string begins.
     * @return string The string parsed from the given offset, or null if the offset is -1 or 65534.
     */
    private function parseStringFromStringTable($offset)
    {
        $stringTable = $this->terminfo[self::KEY_STRING_TABLE];

        $string = null;

        // If offset is -1 or 65534 (due to an issue in xterm-256color)
        // string is taken as missing.
        if (-1 !== $offset && 65534 !== $offset) {

            $length = 0;

            while (($i = $offset + $length) < strlen($stringTable) && ord($stringTable[$i])) {

                $length++;
            }

            $string = substr($stringTable, $offset, $length);
        }

        return $string;
   }
}
