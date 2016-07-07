<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ZerusTech\Component\Terminal\Tool;

/**
 * The generic class that abstracts all terminal tools.
 *
 * A terminal tool is a class that controls a resource: cursor, screen and etc.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
abstract class AbstractTool
{
    /**
     * @var ZerusTech\Component\Terminal\Terminal Current terminal.
     */
    protected $terminal;

    /**
     * Constructor.
     *
     * @param ZerusTech\Component\Terminal\Terminal Current terminal.
     */
    public function __construct($terminal)
    {
        $this->terminal = $terminal;
    }

    /**
     * Returns current terminal.
     *
     * @return ZerusTech\Component\Terminal\Terminal Current terminal.
     */
    public function getTerminal()
    {
        return $this->terminal;
    }

    /**
     * Sends a command to current terminal.
     *
     * @param string $cmd Command sent to the terminal.
     * @return Tool Current instance.
     */
    protected function send($cmd)
    {
        if ($cmd) {

            $this->terminal->getOutput()->write($cmd);
        }

        return $this;
    }

    /**
     * Returns value of the given string capability.
     *
     * @param string $name The capability name.
     * @return string The capability value.
     */
    protected function getString($name)
    {
        return $this->terminal->getTerminfo()->getString($name);
    }

    /**
     * Returns value of the given number capability.
     *
     * @param string $name The capability name.
     * @return int The capability value.
     */
    protected function getNumber($name)
    {
        return $this->terminal->getTerminfo()->getNumber($name);
    }

    /**
     * Returns value of the given boolean capability.
     *
     * @param string $name The capability name.
     * @return bool The capability value.
     */
    protected function getBoolean($name)
    {
        return $this->terminal->getTerminfo()->getBoolean($name);
    }
}
