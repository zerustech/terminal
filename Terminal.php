<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace ZerusTech\Component\Terminal;

use ZerusTech\Component\Terminal\Terminfo\Terminfo;
use ZerusTech\Component\Terminal\Tool\CursorTool;
use ZerusTech\Component\Terminal\Tool\ScreenTool;
use ZerusTech\Component\IO\Stream\Input\FileInputStream;
use ZerusTech\Component\IO\Stream\Output\FileOutputStream;
use ZerusTech\Component\IO\Stream\Input\InputStreamInterface;
use ZerusTech\Component\IO\Stream\Output\OutputStreamInterface;

/**
 * The Terminal class is an abstract of the terminal window.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class Terminal
{
    /**
     * @var string Current tty name (e.g, '/dev/ttys001').
     */
    private $tty;

    /**
     * @var string Current terminal name (e.g, 'xterm-256color').
     */
    private $term;

    /**
     * @var string The previously stored terminal configuration.
     */
    private $previousConfig;

    /**
     * @var string Terminal mode. 'normal', by default.
     */
    private $mode = 'normal';

    /**
     * @var Terminfo Terminfo instance of current terminal.
     */
    private $terminfo = null;

    /**
     * @var CursorTool CursorTool instance of current terminal.
     */
    private $cursor = null;

    /**
     * @var ScreenTool ScreenTool instance of current terminal.
     */
    private $screen = null;

    /**
     * @var InputStreamInterface Input stream of current terminal.
     */
    private $input = null;

    /**
     * @var OutputStreamInterface Output stream of current terminal.
     */
    private $output = null;

    /**
     * The associative array of terminal instances.
     *
     * Each instance is indexed by the md5 string of its tty and terminal name.
     *
     * @var Terminal[] The associative array of terminal instances.
     */
    private static $instances = [];

    /**
     * Constructor.
     *
     * This method is private, so don't try to construct a terminal instance.
     * Use {@link \ZerusTech\Component\Terminal\Terminal::instance()} instead.
     *
     * @param string $tty The tty name of current terminal.
     * @param string $term The terminal name of current terminal.
     */
    private function __construct($tty, $term)
    {
        $this->tty = $tty;

        $this->term = $term;

        $this->terminfo = new Terminfo($term);

        $this->cursor = new CursorTool($this);

        $this->screen = new ScreenTool($this);

        $this->input = new FileInputStream('php://stdin', 'rb');

        $this->output = new FileOutputStream('php://stdout', 'wb');
    }

    /**
     * Creates a new terminal instance.
     *
     * @param string $tty The tty name.
     * @param string $term The terminal name.
     * @return Terminal The terminal instance.
     */
    public static function instance($tty = null, $term = null)
    {
        if (null === $tty) {

            $tty = posix_ttyname(STDOUT);
        }

        $key = md5($tty.'/'.$term);

        if (!isset(self::$instances[$key])) {

            self::$instances[$key] = new self($tty, $term);
        }

        return self::$instances[$key];
    }

    /**
     * Changes current terminal to silent stream.
     *
     * Silent mode means:
     * - ``echo`` is disabled
     * - ``canonical`` mode is disabled
     * - processes ``$min`` characters each time
     *
     * This mode is useful for hidding terminal response, or
     * developing advanced CLI applications.
     *
     * When switching to this mode, the original configuration is backed up.
     *
     * @param int $min The minimum characters to be processed, 1 by default.
     * @return Terminal Current instance.
     */
    public function silentStreamMode($min = 1)
    {
        if ('silentStream' != $this->mode) {

            $this->previousConfig = shell_exec("stty -g < {$this->tty}");

            shell_exec("stty -echo -icanon min $min time 0 < {$this->tty}");

            $this->mode = 'silentStream';
        }

        return $this;
    }

    /**
     * Switches back to normal mode.
     *
     * When switching back to normal mode, the previously stored configuration
     * backup is recovered.
     *
     * @return Terminal Current instance.
     */
    public function normalMode()
    {
        if ('normal' != $this->mode) {

            shell_exec("stty {$this->previousConfig} < {$this->tty}");

            $this->mode = 'normal';
        }

        return $this;
    }

    /**
     * Returns cursor tool of current terminal.
     *
     * @return CursorTool The cursor tool.
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    /**
     * Returns screen tool of current terminal.
     *
     * @return ScreenTool The screen tool.
     */
    public function getScreen()
    {
        return $this->screen;
    }

    /**
     * Returns terminfo of current terminal.
     *
     * @return Terminfo The terminfo.
     */
    public function getTerminfo()
    {
        return $this->terminfo;
    }

    /**
     * Returns input stream of current terminal.
     *
     * @return InputStreamInterface The input file.
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Returns output stream of current terminal.
     *
     * @return OutputStreamInterface The output file.
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Returns current tty name.
     * @return string The tty name.
     */
    public function getTty()
    {
        return $this->tty;
    }

    /**
     * Returns current terminal name.
     * @return string The terminal name.
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * Returns the previously stored terminal configuration.
     * @return string The previous configuration.
     */
    public function getPreviousConfig()
    {
        return $this->previousConfig;
    }

    /**
     * Returns mode of current terminal.
     *
     * Valid modes include: ``normal`` and ``silentStream``
     * @return string The temrinal mode.
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Sets input stream for current terminal.
     * @param InputStreamInterface $in The input stream.
     * @return Terminal Current instance.
     */
    public function setInput($input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Sets output stream for current terminal.
     * @param OutputStreamInterface $out The output stream.
     * @return Terminal Current instance.
     */
    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }
}
