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
 * The terminal tool that controls screen.
 *
 * Resources:
 * - https://en.wikipedia.org/wiki/File:Xterm_256color_chart.svg
 * - https://www.freebsd.org/cgi/man.cgi?query=terminfo&sektion=5
 * - http://www.termsys.demon.co.uk/vtansi.htm#colors
 * - https://www.gnu.org/software/screen/manual/html_node/Control-Sequences.html#Control-Sequences
 * - https://github.com/hoaproject/Console/blob/master/Cursor.php
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class ScreenTool extends AbstractTool
{
    /**
     * Table of 256 colors.
     *
     * Each color is defined by the combination of its RGB hexadecimal values.
     * The colorizing commands refer to a color by its key in this color palette.
     *
     * @var array The 256 Color Palette.
     */
    static $colorPalette = [
        '000000', '800000', '008000', '808000', '000080', '800080',
        '008080', 'c0c0c0', '808080', 'ff0000', '00ff00', 'ffff00',
        '0000ff', 'ff00ff', '00ffff', 'ffffff', '000000', '00005f',
        '000087', '0000af', '0000d7', '0000ff', '005f00', '005f5f',
        '005f87', '005faf', '005fd7', '005fff', '008700', '00875f',
        '008787', '0087af', '0087d7', '0087ff', '00af00', '00af5f',
        '00af87', '00afaf', '00afd7', '00afff', '00d700', '00d75f',
        '00d787', '00d7af', '00d7d7', '00d7ff', '00ff00', '00ff5f',
        '00ff87', '00ffaf', '00ffd7', '00ffff', '5f0000', '5f005f',
        '5f0087', '5f00af', '5f00d7', '5f00ff', '5f5f00', '5f5f5f',
        '5f5f87', '5f5faf', '5f5fd7', '5f5fff', '5f8700', '5f875f',
        '5f8787', '5f87af', '5f87d7', '5f87ff', '5faf00', '5faf5f',
        '5faf87', '5fafaf', '5fafd7', '5fafff', '5fd700', '5fd75f',
        '5fd787', '5fd7af', '5fd7d7', '5fd7ff', '5fff00', '5fff5f',
        '5fff87', '5fffaf', '5fffd7', '5fffff', '870000', '87005f',
        '870087', '8700af', '8700d7', '8700ff', '875f00', '875f5f',
        '875f87', '875faf', '875fd7', '875fff', '878700', '87875f',
        '878787', '8787af', '8787d7', '8787ff', '87af00', '87af5f',
        '87af87', '87afaf', '87afd7', '87afff', '87d700', '87d75f',
        '87d787', '87d7af', '87d7d7', '87d7ff', '87ff00', '87ff5f',
        '87ff87', '87ffaf', '87ffd7', '87ffff', 'af0000', 'af005f',
        'af0087', 'af00af', 'af00d7', 'af00ff', 'af5f00', 'af5f5f',
        'af5f87', 'af5faf', 'af5fd7', 'af5fff', 'af8700', 'af875f',
        'af8787', 'af87af', 'af87d7', 'af87ff', 'afaf00', 'afaf5f',
        'afaf87', 'afafaf', 'afafd7', 'afafff', 'afd700', 'afd75f',
        'afd787', 'afd7af', 'afd7d7', 'afd7ff', 'afff00', 'afff5f',
        'afff87', 'afffaf', 'afffd7', 'afffff', 'd70000', 'd7005f',
        'd70087', 'd700af', 'd700d7', 'd700ff', 'd75f00', 'd75f5f',
        'd75f87', 'd75faf', 'd75fd7', 'd75fff', 'd78700', 'd7875f',
        'd78787', 'd787af', 'd787d7', 'd787ff', 'd7af00', 'd7af5f',
        'd7af87', 'd7afaf', 'd7afd7', 'd7afff', 'd7d700', 'd7d75f',
        'd7d787', 'd7d7af', 'd7d7d7', 'd7d7ff', 'd7ff00', 'd7ff5f',
        'd7ff87', 'd7ffaf', 'd7ffd7', 'd7ffff', 'ff0000', 'ff005f',
        'ff0087', 'ff00af', 'ff00d7', 'ff00ff', 'ff5f00', 'ff5f5f',
        'ff5f87', 'ff5faf', 'ff5fd7', 'ff5fff', 'ff8700', 'ff875f',
        'ff8787', 'ff87af', 'ff87d7', 'ff87ff', 'ffaf00', 'ffaf5f',
        'ffaf87', 'ffafaf', 'ffafd7', 'ffafff', 'ffd700', 'ffd75f',
        'ffd787', 'ffd7af', 'ffd7d7', 'ffd7ff', 'ffff00', 'ffff5f',
        'ffff87', 'ffffaf', 'ffffd7', 'ffffff', '080808', '121212',
        '1c1c1c', '262626', '303030', '3a3a3a', '444444', '4e4e4e',
        '585858', '606060', '666666', '767676', '808080', '8a8a8a',
        '949494', '9e9e9e', 'a8a8a8', 'b2b2b2', 'bcbcbc', 'c6c6c6',
        'd0d0d0', 'dadada', 'e4e4e4', 'eeeeee',
    ];

    /**
     * Table of basic color aliases.
     *
     * The colorizing commands refer to a color by its key in this array.
     *
     * @var array The table of basic color aliases.
     */
    static $colorAliases = [
        'black',
        'red',
        'green',
        'yellow',
        'blue',
        'magenta',
        'cyan',
        'white',
    ];

    /**
     * Clears partial or entire screen area.
     *
     * Clears the screen area according to the ``$part`` parameter. Valid part
     * values include: 'all', 'bol', 'eol' and 'eos'.
     *
     * @param string $part The part to be cleared.
     * @return ScreenTool Current instance.
     */
    public function clear($part = 'all')
    {
        $cmd = null;

        switch ($part) {

            case 'all':

                $cmd = $this->getString('clear_screen');

                break;

            case 'bol':

                $cmd = $this->getString('clr_bol');

                break;

            case 'eol':

                $cmd = $this->getString('clr_eol');

                break;

            case 'eos':

                $cmd = $this->getString('clr_eos');

                break;
        }

        return $this->send($cmd);
    }

    /**
     * Deletes lines or characters from current cursor position.
     *
     * Lines are deleted downward, while characters are deleted rightward.
     * The number of lines / characters are given by the ``$count`` parameter.
     * Valid part values include: 'line' and 'character'.
     *
     * @param string $part Controls what to delete.
     * @param int $count The number of parts to be deleted.
     * @return ScreenTool Current instance.
     */
    public function delete($part, $count = 1)
    {
        $cmd = null;

        switch ($part) {

            case 'line':

                $cmd = str_replace('%p1%d', $count, $this->getString('parm_delete_line'));

                break;

            case 'character':

                $cmd = str_repeat($this->getString('delete_character'), $count);

                break;
        }

        return $this->send($cmd);
    }

    /**
     * Inserts lines below current cursor position.
     *
     * @param string $part Controls what to insert. For now, only 'line' is valid.
     * @param int $count Number of lines to be inserted, 1 by default.
     * @return ScreenTool Current instance.
     */
    public function insert($part = 'line', $count = 1)
    {
        $cmd = null;

        switch ($part) {

            case 'line':

                $cmd = str_replace('%p1%d', $count, $this->getString('parm_insert_line'));

                break;
        }

        return $this->send($cmd);

    }

    /**
     * Toggles display modes.
     *
     * Valid modes include: 'none', 'hide', 'bold', 'underscore', 'blink' and
     * 'inverse'. A display mode can be turned on / off by the ``$toggle``
     *
     * parameter. Modes 'none' and 'hide' can only be turned on.
     * @param string $mode The display mode.
     * @param bool $toggle True to turn on the given mode, and false otherwise.
     * @return ScreenTool Current instance.
     */
    public function mode($mode, $toggle = true)
    {
        $cmd = null;

        switch ($mode) {

            case 'none':

                $cmd = "\e[0m";

                break;

            case 'hide':

                $cmd = "\e[8m";

                break;

            case 'bold':

                $cmd = false === $toggle ? "\e[22m" : "\e[1m";

                break;

            case 'underscore':

                $cmd = false === $toggle ? "\e[24m" : "\e[4m";

                break;

            case 'blink':

                $cmd = false === $toggle ? "\e[25m" : "\e[5m";

                break;

            case 'inverse':

                $cmd = false === $toggle ? "\e[27m" : "\e[7m";

                break;
        }

        return $this->send($cmd);
    }

    /**
     * Changes foreground color.
     *
     * The color can be given as a hexadecimal RGB value or aliase.
     *
     * @param string $color The foreground color.
     * @return ScreenTool Current instance.
     */
    public function foregroundColor($color)
    {
        return $this->colorize($color);
    }

    /**
     * Changes background color.
     *
     * @see foregroundColor()
     * @param string $color The background color.
     * @return ScreenTool Current instance.
     */
    public function backgroundColor($color)
    {
        return $this->colorize($color, 40);
    }

    /**
     * Changes foreground or background color according to the ``offset``
     * parameter.
     *
     * Set offset to 30, which is the default value, if you want to change
     * the foreground color and to 40 for the background color.
     *
     * This method is protected, so it should not be called directly. In stead,
     * it is used by the {@link foregroundColor()} and {@link backgroundColor()}
     * methods.
     *
     * @param string $color The color to replace current color.
     * @param int $offset 30 for foreground and 40 for background.
     * @return ScreenTool Current instance.
     */
    protected function colorize($color, $offset = 30)
    {
        $cmd = null;

        // If color is given as an alias and it finds a match,
        // Use the simple command to set the color.
        // The offset is applied on the color code.
        // Refer to http://www.termsys.demon.co.uk/vtansi.htm#fonts for details.
        if (null !== ($index = $this->findColorIndexByAlias($color))) {

            $index += $offset;

            $cmd = "\033[{$index}m";

        } else if (null !== ($index = $this->findColorIndexByRGB($color))) {
            // Otherwise, if hexadecimal RGB color is given and it finds a match
            // in the 256-color table, use the complex command to set color.
            // In this case, offset is applied on the prefix.
            // Refer to ``infocmp -L xterm-color256`` for details.

            $prefix = 8 + $offset;

            $cmd = "\033[{$prefix};5;{$index}m";
        }

        return $this->send($cmd);
    }

    /**
     * Maps hexadecimal RGB value to its index in the 256-color table.
     *
     * Color index shall be used in the complex color command.
     * This method tries to find the color in the 256-color table that
     * is closest to the given RGB color. So the hexadecimal RGB value does not
     * have to be exactly matched.
     *
     * @param string $rgbHex The hexadecimal RGB value.
     * @return int The color index.
     */
    protected function findColorIndexByRGB($rgbHex)
    {
        $color = null;

        if (256 <= $this->getNumber('max_colors') && preg_match('/^[0-9a-fA-F]{6}$/', $rgbHex)) {

            $distance = null;

            $rgbDec = hexdec($rgbHex);

            $red = ($rgbDec >> 16) & 255;

            $green = ($rgbDec >> 8) & 255;

            $blue= $rgbDec & 255;

            foreach (static::$colorPalette as $i => $itemHex) {

                $itemDec = hexdec($itemHex);

                $itemRed = ($itemDec >> 16) & 255;

                $itemGreen = ($itemDec >> 8) & 255;

                $itemBlue = $itemDec & 255;

                $d = sqrt(pow($red - $itemRed, 2) + pow($green - $itemGreen, 2) + pow($blue - $itemBlue, 2));

                if (null === $distance || $d < $distance) {

                    $distance = $d;

                    $color = $i;
                }
            }
        }

        return $color;
    }

    /**
     * Finds color index by color alias.
     *
     * @param string $alias The color alias.
     * @return int The color index.
     */
    protected function findColorIndexByAlias($alias)
    {
        $color = null;

        if (in_array($alias, static::$colorAliases)) {

            $color = array_search($alias, static::$colorAliases);
        }

        return $color;
    }
}
