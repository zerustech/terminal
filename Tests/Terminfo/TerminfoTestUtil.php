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
 * Helper class for Terminfo tests.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class TerminfoTestUtil
{
    /**
     * @var array Array of possible terminfo directories.
     */
    public static $terminfoPaths = [
            '/terminfo',
            '/home/.terminfo',
            '/terminfo-dirs/dir1',
            '/terminfo-dirs/dir2',
            '/usr/share/terminfo',
            '/usr/share/lib/terminfo',
            '/lib/terminfo',
            '/usr/lib/terminfo',
            '/usr/local/share/terminfo',
            '/usr/local/share/lib/terminfo',
            '/usr/local/lib/terminfo',
            '/usr/local/ncurses/lib/terminfo',
            '/opt/local/share/terminfo',
            '/opt/local/lib/terminfo',
    ];

    /**
     * @var array Array of global variables for additional temrinfo directories.
     */
    public static $terminfoVariableNames = [
            0 => 'TERMINFO',
            1 => 'HOME',
            3 => 'TERMINFO_DIRS',
    ];

    /**
     * @var string The fixtures directory.
     */
    protected static $base = '/../Fixtures/';

    /**
     * Parses all capability names from Caps file:
     *
     *     [
     *         'booleans' => [...],
     *         'numbers' => [...],
     *         'strings' => [...],
     *     ]
     *
     * @return array The capability names.
     */
    public static function getCapabilityNames()
    {
        $file = __DIR__.static::$base.'ncurses-6.0/include/Caps';

        $fp = fopen($file, 'rb');

        $names = [
            Terminfo::KEY_BOOLEANS => [],
            Terminfo::KEY_NUMBERS => [],
            Terminfo::KEY_STRINGS => []
        ];

        while ($line = fgets($fp)) {

            if (preg_match('/^#.*$/', $line)) {

                continue;
            }

            $line = preg_replace("/[\t]+/", ' ', $line);

            if (count(explode(' ', $line)) < 3) {

                continue;
            }

            list ($name, $alias, $type) = explode(' ', $line);

            $key = null;

            switch ($type) {

                case 'bool':

                    $key = Terminfo::KEY_BOOLEANS;

                    break;

                case 'num':

                    $key = Terminfo::KEY_NUMBERS;

                    break;

                case 'str':

                    $key = Terminfo::KEY_STRINGS;

                    break;
            }

            if ($key) {

                $names[$key][] = $name;
            }
        }

        return $names;
    }

    /**
     * Resolves terminfo file name based on terminal name.
     * If terminal name is null, or doesn't match any element in ['xterm',
     * 'xterm-256color'], 'xterm' is returned, otherwise, the terminal name is
     * used.
     *
     * @return string The terminfo file name.
     */
    public static function getTermFileForTerm($term)
    {
        $files = ['xterm', 'xterm-256color'];

        $file = 'xterm';

        if (null === $term || !in_array($term, $files)) {

            $file = 'xterm';

        } else {

            $file = $term;
        }

        return $file;
    }

    /**
     * Creates a new terminfo instance.
     *
     * It sets $_SERVER['TERM'] with the value of ``$global`` parameter, then
     * resolves a terminfo file name from the terminal name and creates terminfo
     * files accordingly.
     *
     * Finally, it creates a new Terminfo instance.
     *
     * @param string $global The value for $_SERVER['TERM'], defaults to
     * 'xterm-256color'.
     * @param string $term The terminal name, defaults to 'xterm-256color'.
     * @param string $root The prefix of terminfo directories, defaults to 'vfs://root'.
     * @return Terminfo The terminfo instance.
     * @see getTermFileForTerm()
     * @see setupTerminfoFiles()
     */
    public static function getTerminfoInstance($global = 'xterm-256color', $term = 'xterm-256color', $root = 'vfs://root')
    {
        $_SERVER['TERM'] = $global;

        $termFile = static::getTermFileForTerm($term);

        static::setupTerminfoFiles($termFile, $root);

        $terminfo = new Terminfo($term, $root);

        return $terminfo;
    }

    /**
     * Creates terminfo directories and files in vfs virtual file system.
     *
     * @param string $termFile The terminfo file name.
     * @param string $root The prefix of all terminfo directories.
     * @return void
     */
    public static function setupTerminfoFiles($termFile = 'xterm-256color', $root = 'vfs://root')
    {
        $hexa = dechex(ord($termFile[0]));

        $alpha = $termFile[0];

        list($protocol, $base) = explode('://', $root);

        vfsStream::setup($base);

        // Creates terminfo files.
        foreach (static::$terminfoPaths as $path) {

            $hexaBasePath = $base.$path.'/'.$hexa;

            $alphaBasePath = $base.$path.'/'.$alpha;

            mkdir(vfsStream::url($hexaBasePath), 0777, true);

            mkdir(vfsStream::url($alphaBasePath), 0777, true);

            copy(__DIR__.static::$base.$termFile, vfsStream::url($hexaBasePath.'/'.$termFile));

            copy(__DIR__.static::$base.$termFile, vfsStream::url($alphaBasePath.'/'.$termFile));
        }
    }
}
