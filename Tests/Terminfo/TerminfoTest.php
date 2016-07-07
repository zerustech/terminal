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

use org\bovigo\vfs\vfsStream;
use ZerusTech\Component\Terminal\Terminfo\Terminfo;
use ZerusTech\Component\Terminal\Terminfo\Parser;

/**
 * Test case for terminfo.
 */
class TerminfoTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDefaultTerm()
    {
        $_SERVER['TERM'] = 'xterm-256color';

        $this->assertEquals('xterm-256color', Terminfo::getDefaultTerm());

        unset($_SERVER['TERM']);

        $this->assertEquals('xterm', Terminfo::getDefaultTerm());

        $_SERVER['TERM'] = null;

        $this->assertEquals('xterm', Terminfo::getDefaultTerm());
    }

    /**
     * Provides $_SERVER['TERM'], term and terminfo file name.
     */
    public function getGlobalTermAndTermAndTermFile()
    {
        return [
            [null, 'xterm-256color', 'xterm-256color'], // term matches term file
            [null, 'xterm', 'xterm'], // term matches term file
            [null, 'ansi', 'xterm'], // term fails to match, match with 'xterm'
            ['xterm-256color', null, 'xterm'], // term is null, global fails to match, so match with 'xterm'
            ['xterm', null, 'xterm'], // term is null, global matches term file.
        ];
    }

    /**
     * @dataProvider getGlobalTermAndTermAndTermFile
     * @depends testGetDefaultTerm
     */
    public function testGetTerminfoPath($global, $term, $termFile)
    {
        $_SERVER['TERM'] = $global;

        $hexa = dechex(ord($termFile[0]));

        $alpha = $termFile[0];

        $protocol = 'vfs';

        $base = 'root';

        $root = "$protocol://$base";

        // Creates terminfo files.
        TerminfoTestUtil::setupTerminfoFiles($termFile, $root);

        // Sets up global variables.
        $_SERVER['TERMINFO'] = '/terminfo';

        $_SERVER['HOME'] = '/home';

        $_SERVER['TERMINFO_DIRS'] = '/terminfo-dirs/dir1:/terminfo-dirs/dir2';

        // Tests terminfo file in all possible locations one by one.
        foreach (TerminfoTestUtil::$terminfoPaths as $i => $path) {

            $hexaPath = $base.TerminfoTestUtil::$terminfoPaths[$i].'/'.$hexa.'/'.$termFile;

            $alphaPath = $base.TerminfoTestUtil::$terminfoPaths[$i].'/'.$alpha.'/'.$termFile;

            $this->assertEquals(vfsStream::url($hexaPath), Terminfo::getTerminfoPath($term, $root));

            unlink(vfsStream::url($hexaPath));

            $this->assertEquals(vfsStream::url($alphaPath), Terminfo::getTerminfoPath($term, $root));

            if (array_key_exists($i, TerminfoTestUtil::$terminfoVariableNames)) {

                unset($_SERVER[TerminfoTestUtil::$terminfoVariableNames[$i]]);

                $this->assertEquals(
                    vfsStream::url($base.TerminfoTestUtil::$terminfoPaths[$i + 1].'/'.$hexa.'/'.$termFile),
                    Terminfo::getTerminfoPath($term, $root)
                );

            }

            unlink(vfsStream::url($alphaPath));
        }
    }

    /**
     * @depends testGetTerminfoPath
     * @dataProvider getGlobalTermAndTermAndTermFile
     */
    public function  testConstructor($global, $term, $termFile)
    {
        $root = 'vfs://root';

        $terminfo = TerminfoTestUtil::getTerminfoInstance($global, $term, $root);

        $info = $terminfo->getParser()->getTerminfo();

        $this->assertEquals($term, $terminfo->getTerm());

        $this->assertEquals($root, $terminfo->getRoot());

        $this->assertEquals(
            $info[Parser::KEY_RAW_DATA],
            file_get_contents(__DIR__.'/../Fixtures/'.$termFile)
        );
    }

    /**
     * Tests getBooleanNames(), getNumberNames() and getStringNames()
     */
    public function testCapabilityNames()
    {
        $expectedNames = TerminfoTestUtil::getCapabilityNames();

        $keys = array_keys($expectedNames);

        $actualNames = array_combine(
            $keys,
            [
                Terminfo::getBooleanNames(),
                Terminfo::getNumberNames(),
                Terminfo::getStringNames(),
            ]
        );

        foreach ($keys as $key) {

            for ($i = 0; $i < count($expectedNames[$key]); $i++) {

                $this->assertEquals($expectedNames[$key][$i], $actualNames[$key][$i]);
            }
        }
    }

    /**
     * @testCapabilityNames
     */
    public function testGetTerminfo()
    {
        $terminfo = TerminfoTestUtil::getTerminfoInstance();

        $keys = [
            Terminfo::KEY_BOOLEANS,
            Terminfo::KEY_NUMBERS,
            Terminfo::KEY_STRINGS,
        ];

        $expectedNames = TerminfoTestUtil::getCapabilityNames();

        $expectedTerminfo = $terminfo->getParser()->parse()->getTerminfo();

        $actualTerminfo = $terminfo->getTerminfo();

        $actualNames = array_combine(
            $keys,
            [
                array_keys($actualTerminfo[Terminfo::KEY_BOOLEANS]),
                array_keys($actualTerminfo[Terminfo::KEY_NUMBERS]),
                array_keys($actualTerminfo[Terminfo::KEY_STRINGS]),
            ]
        );

        foreach ($keys as $key) {

            for($i = 0; $i < count($actualNames[$key]); $i++) {

                $this->assertEquals($expectedNames[$key][$i], $actualNames[$key][$i]);

                $this->assertEquals($expectedTerminfo[$key][$i], $actualTerminfo[$key][$actualNames[$key][$i]]);
            }
        }
    }

    public function testCapabilityValues()
    {
        $terminfo = TerminfoTestUtil::getTerminfoInstance();

        $keys = [
            Terminfo::KEY_BOOLEANS,
            Terminfo::KEY_NUMBERS,
            Terminfo::KEY_STRINGS,
        ];

        $methods = [
            Terminfo::KEY_BOOLEANS => 'getBoolean',
            Terminfo::KEY_NUMBERS => 'getNumber',
            Terminfo::KEY_STRINGS => 'getString'
        ];

        $expectedNames = TerminfoTestUtil::getCapabilityNames();

        $expectedTerminfo = $terminfo->getParser()->parse()->getTerminfo();

        foreach ($keys as $key) {

            for ($i = 0; $i < count($expectedNames[$key]); $i++) {

                $expected = isset($expectedTerminfo[$key][$i]) ? $expectedTerminfo[$key][$i] : null;

                $actual = call_user_func(array($terminfo, $methods[$key]), $expectedNames[$key][$i]);

                $this->assertEquals($expected, $actual);
            }
        }
    }
}
