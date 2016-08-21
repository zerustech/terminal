<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace ZerusTech\Component\Terminal\Tool;

/**
 * The terminal tool that controls cursor.
 *
 * Resources:
 * - http://www.termsys.demon.co.uk/vtansi.htm
 * - https://www.gnu.org/software/screen/manual/html_node/Control-Sequences.html#Control-Sequences
 * - https://www.freebsd.org/cgi/man.cgi?query=terminfo&sektion=5
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class CursorTool extends AbstractTool
{
    /**
     * Gets current cursor position.
     *
     * The structure is as follows:
     *
     *     [
     *         "row" => ...,
     *         "col" => ...,
     *     ]
     *
     * @return array Current cursor position.
     */
    public function getPosition()
    {
        $this->terminal->silentStreamMode();

        // The user7 capability is for querying cursor position.
        $cmd = $this->getString('user7');

        $this->send($cmd);

        $this->terminal->getInput()->read($bytes, 2); // skips \e and [

        $response = '';

        $row = 0;

        $col = 0;

        $buffer = '';

        // The response format is ``"\033[{row};{column}R"``
        while (true) {

            $this->terminal->getInput()->read($c);

            switch ($c) {

                case 'R':

                    $col = $buffer;

                    break 2;

                case ';':

                    $row = $buffer;

                    $buffer = '';

                    break;

                default:

                    $buffer .= $c;
            }
        }

        $this->terminal->normalMode();

        $response = ['row' => $row, 'col' => $col];

        return $response;
    }

    /**
     * Moves cursor to an absolute position.
     *
     * When row or column is null, current row or column is used.
     *
     * @param string $row The row number.
     * @param string $col The column number.
     * @return CursorTool Current instance.
     */
    public function moveTo($row = null, $col = null)
    {
        if (null === $row || null === $col) {

            $position = $this->getPosition();
        }

        $row = (null === $row) ? $position['row'] : $row;

        $col = (null === $col) ? $position['col'] : $col;

        $cmd = str_replace(['%i%p1%d', '%p2%d'], [$row, $col], $this->getString('cursor_address'));

        return $this->send($cmd);
    }

    /**
     * Moves the cursor a relative amount of steps in a given direction.
     *
     * The valid directions include: 'up', 'right', 'down', 'left', 'home' and
     * 'bol' (bottom of line)
     *
     * @param string $direction The direction of cursor movement.
     * @param int $steps The steps to move, 1 by default.
     * @return CursorTool Current instance.
     */
    public function move($direction, $steps = 1)
    {
        switch ($direction) {

            case 'up':

                $cmd = str_replace('%p1%d', $steps, $this->getString('parm_up_cursor'));

                break;

            case 'right':

                $cmd = str_replace('%p1%d', $steps, $this->getString('parm_right_cursor'));

                break;

            case 'down':

                $cmd = str_replace('%p1%d', $steps, $this->getString('parm_down_cursor'));

                break;

            case 'left':

                $cmd = str_replace('%p1%d', $steps, $this->getString('parm_left_cursor'));

                break;

            case 'home':

                return $this->moveTo(0, 0);

            case 'bol':

                return $this->moveTo(null, 0);
        }

        return $this->send($cmd);
    }

    /**
     * Saves current cursor position.
     *
     * @return CursorTool Current instance.
     */
    public function save()
    {
        $cmd = $this->getString('save_cursor');

        return $this->send($cmd);
    }

    /**
     * Restores cursor to the last saved position.
     *
     * @return CursorTool Current instance.
     */
    public function restore()
    {
        $cmd = $this->getString('restore_cursor');

        return $this->send($cmd);
    }

    /**
     * Hides cursor.
     *
     * @return CursorTool Current instance.
     */
    public function hide()
    {
        $cmd = $this->getString('cursor_invisible');

        return $this->send($cmd);
    }

    /**
     * Reveals cursor.
     *
     * @return CursorTool Current instance.
     */
    public function show()
    {
        $cmd = $this->getString('cursor_visible');

        return $this->send($cmd);
    }
}
