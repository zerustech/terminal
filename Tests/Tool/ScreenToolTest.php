<?php

namespace ZerusTech\Component\Terminal\Tests\Tool;

use ZerusTech\Component\Terminal\Terminal;
use ZerusTech\Component\Terminal\Tool\ScreenTool;
use ZerusTech\Component\Terminal\Tests\TerminalTestUtil;
use ZerusTech\Component\Terminal\Tests\Tool\ToolTestUtil;
use ZerusTech\Component\IO\Stream\Input\FileInputStream;
use ZerusTech\Component\IO\Stream\Input\StringInputStream;
use ZerusTech\Component\IO\Stream\Output\FileOutputStream;
use ZerusTech\Component\Threaded\Stream\Input\PipedInputStream;
use ZerusTech\Component\Threaded\Stream\Output\PipedOutputStream;

class ScreenToolTest extends \PHPUnit_Framework_TestCase
{
    private $screenToolFQN = 'ZerusTech\Component\Terminal\Tool\ScreenTool';

    /**
     * @dataProvider dataForTestClear
     */
    public function testClear($part, $cmd, $length)
    {
        $tool = ToolTestUtil::getScreenToolInstance();

        $output = new PipedOutputStream();

        $buffer = new \Threaded();
        $monitor = new PipedInputStream($buffer, $output);

        $terminal = $tool->getTerminal();
        $terminal->setOutput($output);

        $tool->clear($part);
        $monitor->read($bytes, $length);
        $this->assertEquals($cmd, $bytes);
    }

    public function dataForTestClear()
    {
        return [
            ['all', "\033[H\033[2J", 7],
            ['bol', "\033[1K", 4],
            ['eol', "\033[K", 3],
            ['eos', "\033[J", 3]
        ];
    }

    /**
     * @dataProvider dataForTestDelete
     */
    public function testDelete($part, $steps, $cmd, $length)
    {
        $tool = ToolTestUtil::getScreenToolInstance();

        $output = new PipedOutputStream();

        $buffer = new \Threaded();
        $monitor = new PipedInputStream($buffer, $output);

        $terminal = $tool->getTerminal();
        $terminal->setOutput($output);

        $tool->delete($part, $steps);
        $monitor->read($bytes, $length);
        $this->assertEquals($cmd, $bytes);
    }

    public function dataForTestDelete()
    {
        return [
            ['line', 1, "\033[1M", 4],
            ['line', 10, "\033[10M", 5],
            ['character', 1, "\033[P", 3],
            ['character', 10, str_repeat("\033[P", 10), 30]
        ];
    }

    public function testInsert()
    {
        $tool = ToolTestUtil::getScreenToolInstance();

        $output = new PipedOutputStream();

        $buffer = new \Threaded();
        $monitor = new PipedInputStream($buffer, $output);

        $terminal = $tool->getTerminal();
        $terminal->setOutput($output);

        $tool->insert();
        $monitor->read($bytes, 4);
        $this->assertEquals("\033[1L", $bytes);

        $tool->insert('line', 10);
        $monitor->read($bytes, 5);
        $this->assertEquals("\033[10L", $bytes);
    }

    /**
     * @dataProvider dataForTestMode
     */
    public function testMode($mode, $toggle, $cmd, $length)
    {
        $tool = ToolTestUtil::getScreenToolInstance();

        $output = new PipedOutputStream();

        $buffer = new \Threaded();
        $monitor = new PipedInputStream($buffer, $output);

        $terminal = $tool->getTerminal();
        $terminal->setOutput($output);

        $tool->mode($mode, $toggle);
        $monitor->read($bytes, $length);
        $this->assertEquals($cmd, $bytes);
    }

    public function dataForTestMode()
    {
        return [
            ['none', true, "\033[0m", 4],
            ['hide', true, "\033[8m", 4],
            ['bold', false, "\033[22m", 5],
            ['bold', true, "\033[1m", 4],
            ['underscore', false, "\033[24m", 5],
            ['underscore', true, "\033[4m", 4],
            ['blink', false, "\033[25m", 5],
            ['blink', true, "\033[5m", 4],
            ['inverse', false, "\033[27m", 5],
            ['inverse', true, "\033[7m", 4],
        ];
    }

    /**
     * @dataProvider dataForTestFindColorIndexByAlias
     */
    public function testFindColorIndexByAlias($alias, $index)
    {
        $tool = ToolTestUtil::getScreenToolInstance();

        $reflection = new \ReflectionClass($this->screenToolFQN);
        $method = $reflection->getMethod('findColorIndexByAlias');
        $method->setAccessible(true);
        $this->assertEquals($index, $method->invokeArgs($tool, [$alias]));
    }

    public function dataForTestFindColorIndexByAlias()
    {
        $data = [];

        foreach (ScreenTool::$colorAliases as $index => $alias) {

            $data[] = [$alias, $index];
        }

        return $data;
    }

    /**
     * @dataProvider dataForTestFindColorIndexByRGB
     */
    public function testFindColorIndexByRGB($rgb, $index)
    {
        $tool = ToolTestUtil::getScreenToolInstance();

        $reflection = new \ReflectionClass($this->screenToolFQN);
        $method = $reflection->getMethod('findColorIndexByRGB');
        $method->setAccessible(true);
        $this->assertEquals($index, $method->invokeArgs($tool, [$rgb]));
    }

    public function dataForTestFindColorIndexByRGB()
    {
        $data = [];

        foreach (ScreenTool::$colorPalette as $index => $rgb) {

            $indexes = array_keys(ScreenTool::$colorPalette, $rgb);

            $index = $indexes[0];

            $data[] = [$rgb, $index];
        }

        return $data;
    }

    /**
     * @dataProvider dataForTestFindColorIndexByClosestRGB
     */
    public function testFindColorIndexByClosestRGB($rgb, $index)
    {
        $tool = ToolTestUtil::getScreenToolInstance();

        $reflection = new \ReflectionClass($this->screenToolFQN);
        $method = $reflection->getMethod('findColorIndexByRGB');
        $method->setAccessible(true);
        $this->assertEquals($index, $method->invokeArgs($tool, [$rgb]));
    }

    public function dataForTestFindColorIndexByClosestRGB()
    {
        return [
            ['000001', 0],
            ['800001', 1],
            ['008001', 2],
            ['808001', 3],
            ['000081', 4],
            ['800081', 5],
        ];
    }

    /**
     * @dataProvider dataForTestColorize
     */
    public function testColorize($color, $cmd, $index, $type)
    {
        $reflection = new \ReflectionClass($this->screenToolFQN);
        $method = $reflection->getMethod('colorize');
        $method->setAccessible(true);

        $tool = ToolTestUtil::getScreenToolInstance();

        $output = new PipedOutputStream();

        $buffer = new \Threaded();
        $monitor = new PipedInputStream($buffer, $output);

        $terminal = $tool->getTerminal();
        $terminal->setOutput($output);

        $offsets = [30, 40];

        foreach ($offsets as $offset) {

            if ('alias' === $type) {

                $command = sprintf($cmd, $index + $offset);

            } else {

                $command = sprintf($cmd, (8 + $offset), $index);
            }

            $method->invokeArgs($tool, [$color, $offset]);

            $monitor->read($bytes, strlen($command));

            $this->assertEquals($command, $bytes);
        }
    }

    public function dataForTestColorize()
    {
        $colors = [];

        foreach (ScreenTool::$colorAliases as $index => $alias) {

            $colors[] = [$alias, "\033[%dm", $index, 'alias'];
        }

        foreach (ScreenTool::$colorPalette as $rgb) {

            $indexes = array_keys(ScreenTool::$colorPalette, $rgb);

            $index = $indexes[0];

            $cmd = "\033[%d;5;%dm";

            $colors[] = [$rgb, $cmd, $index, 'rgb'];
        }

        return $colors;
    }

    public function testForegroundAndBackgroundColor()
    {
        $tool = $this
            ->getMockBuilder($this->screenToolFQN)
            ->setMethods(['colorize'])
            ->disableOriginalConstructor()
            ->getMock();

        $tool
            ->expects($this->exactly(2))
            ->method('colorize')
            ->withConsecutive(
                ['hello'],
                ['world', 40]
            );

        $tool->foregroundColor('hello');

        $tool->backgroundColor('world');
    }
}
