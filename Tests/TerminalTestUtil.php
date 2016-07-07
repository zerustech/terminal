<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace ZerusTech\Component\Terminal\Tests;

use ZerusTech\Component\Terminal\Terminal;
use ZerusTech\Component\Terminal\Tests\Terminfo\TerminfoTestUtil;

/**
 * Helper class for Terminal tests.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class TerminalTestUtil
{
    /**
     * Creates a new terminal instance.
     *
     * It sets $_SERVER['TERM'] with the value of ``$global`` parameter, then
     * resolves a terminfo file name from the terminal name and creates terminfo
     * files accordingly.
     *
     * Finally, it creates a new Terminal instance.
     *
     * @param string $global The value for $_SERVER['TERM'], defaults to
     * 'xterm-256color'.
     * @param string $tty The tty name.
     * @param string $term The terminal name, defaults to 'xterm-256color'.
     * @param string $termFile The terminfo file name.
     * @return Terminfo The terminfo instance.
     * @see getTermFileForTerm()
     * @see setupTerminfoFiles()
     */
    public static function getTerminalInstance($global = 'xterm-256color', $tty = null, $term = 'xterm-256color', $termFile = 'xterm-256color')
    {
        $_SERVER['TERM'] = $global;

        // $termFile = TerminfoTestUtil::getTermFileForTerm($term);

        TerminfoTestUtil::setupTerminfoFiles($termFile);

        return Terminal::instance($tty, $term);
    }
}
