<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For full copyright and license information, please view the LICENSE file that
 * was distributed with this source code.
 */

namespace ZerusTech\Component\Terminal\Tests;

use ZerusTech\Component\Terminal\Terminal;
use ZerusTech\Component\Terminal\File\File;
use ZerusTech\Component\IO\Stream\Input\FileInputStream;
use ZerusTech\Component\IO\Stream\Output\FileOutputStream;

/**
 * Test case for terminal.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class TerminalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Provides $_SERVER['TERM'], term and terminfo file name.
     */
    public function getGlobalTermAndTermAndTermFile()
    {
        return [
            [null, 'xterm-256color', 'xterm-256color'],
            [null, 'xterm', 'xterm'],
            [null, 'ansi', 'xterm'],
            ['xterm-256color', null, 'xterm'],
            ['xterm', null, 'xterm'],
        ];
    }

    /**
     * @dataProvider getGlobalTermAndTermAndTermFile
     */
    public function testInstance($global = null, $term = null, $termFileName = 'xterm-256color')
    {
        $tty = posix_ttyname(STDOUT);

        $terminal = TerminalTestUtil::getTerminalInstance($global, $tty, $term, $termFileName);

        $this->assertEquals($tty, $terminal->getTty());

        $this->assertEquals($term, $terminal->getTerm());

        $this->assertNull($terminal->getPreviousConfig());

        $this->assertEquals('normal', $terminal->getMode());

        $this->assertEquals($term, $terminal->getTerminfo()->getTerm());

        $this->assertSame($terminal, $terminal->getCursor()->getTerminal());

        $this->assertSame($terminal, $terminal->getScreen()->getTerminal());

        $oldInput = $terminal->getInput();

        $oldOutput = $terminal->getOutput();

        $this->assertEquals('php://stdin', $terminal->getInput()->getSource());

        $this->assertEquals('rb', $terminal->getInput()->getMode());

        $this->assertEquals('php://stdout', $terminal->getOutput()->getSource());

        $this->assertEquals('wb', $terminal->getOutput()->getMode());

        $newInput = new FileInputStream('php://memory', 'rb');

        $newOutput = new FileOutputStream('php://memory', 'wb');

        $terminal->setInput($newInput);

        $terminal->setOutput($newOutput);

        $this->assertEquals('php://memory', $terminal->getInput()->getSource());

        $this->assertEquals('rb', $terminal->getInput()->getMode());

        $this->assertEquals('php://memory', $terminal->getOutput()->getSource());

        $this->assertEquals('wb', $terminal->getOutput()->getMode());

        $terminal->setInput($oldInput);

        $terminal->setOutput($oldOutput);
    }

    public function testStreamMode()
    {
        $terminal = TerminalTestUtil::getTerminalInstance();

        $config = shell_exec("stty -g < {$terminal->getTty()}");

        $terminal->silentStreamMode();

        $this->assertEquals('silentStream', $terminal->getMode());

        $this->assertEquals($config, $terminal->getPreviousConfig());

        $terminal->normalMode();

        $this->assertEquals('normal', $terminal->getMode());

        $this->assertEquals($config, $terminal->getPreviousConfig());
    }
}
