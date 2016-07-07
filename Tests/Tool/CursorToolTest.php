<?php

namespace ZerusTech\Component\Terminal\Tests\Tool;

use ZerusTech\Component\Terminal\Terminal;
use ZerusTech\Component\Terminal\Tool\CursorTool;
use ZerusTech\Component\Terminal\Tests\TerminalTestUtil;
use ZerusTech\Component\Terminal\Tests\Tool\ToolTestUtil;
use ZerusTech\Component\IO\Stream\Input\FileInputStream;
use ZerusTech\Component\IO\Stream\Input\StringInputStream;
use ZerusTech\Component\IO\Stream\Output\FileOutputStream;
use ZerusTech\Component\Threaded\Stream\Input\PipedInputStream;
use ZerusTech\Component\Threaded\Stream\Output\PipedOutputStream;

class CursorToolTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPosition()
    {
        $tool = ToolTestUtil::getCursorToolInstance();

        $input = new StringInputStream("\033[10;20R");

        $output = new PipedOutputStream();

        $buffer = new \Threaded();
        $monitor = new PipedInputStream($buffer, $output);

        $terminal = $tool->getTerminal();
        $terminal->setInput($input);
        $terminal->setOutput($output);

        $this->assertEquals(['row' => 10, 'col' => 20], $tool->getPosition());

        $this->assertEquals("\033[6n", $monitor->read(4));
    }

    /**
     * @dataProvider dataForTestMoveTo
     */
    public function testMoveTo($row, $col, $cmd, $length)
    {
        $tool = ToolTestUtil::getCursorToolInstance();

        $output = new PipedOutputStream();

        $buffer = new \Threaded();
        $monitor = new PipedInputStream($buffer, $output);

        $terminal = $tool->getTerminal();
        $terminal->setOutput($output);

        $terminal->setInput(new StringInputStream("\033[10;10R"));
        $tool->moveTo($row, $col);
        $this->assertEquals($cmd, $monitor->read($length));
    }

    public function dataForTestMoveTo()
    {
        return [
            [100, 200, "\033[100;200H", 10],
            [null, 200, "\033[6n\033[10;200H", 13],
            [200, null, "\033[6n\033[200;10H", 13],
            [null, null, "\033[6n\033[10;10H", 12],
        ];
    }

    /**
     * @dataProvider dataForTestMove
     */
    public function testMove($part, $steps, $cmd, $length)
    {
        $tool = ToolTestUtil::getCursorToolInstance();

        $output = new PipedOutputStream();

        $buffer = new \Threaded();
        $monitor = new PipedInputStream($buffer, $output);

        $terminal = $tool->getTerminal();
        $terminal->setOutput($output);

        $terminal->setInput(new StringInputStream("\033[10;10R"));
        $tool->move($part, $steps);
        $this->assertEquals($cmd, $monitor->read($length));
    }

    public function dataForTestMove()
    {
        return [
            ['up', 1, "\033[1A", 4],
            ['up', 10, "\033[10A", 5],
            ['right', 1, "\033[1C", 4],
            ['right', 10, "\033[10C", 5],
            ['down', 1, "\033[1B", 4],
            ['down', 10, "\033[10B", 5],
            ['left', 1, "\033[1D", 4],
            ['left', 10, "\033[10D", 5],
            ['home', 1, "\033[0;0H", 6],
            ['bol', 1, "\033[6n\033[10;0H", 11],
        ];
    }

    public function testSaveAndRestore()
    {
        $tool = ToolTestUtil::getCursorToolInstance();

        $output = new PipedOutputStream();

        $buffer = new \Threaded();
        $monitor = new PipedInputStream($buffer, $output);

        $terminal = $tool->getTerminal();
        $terminal->setOutput($output);

        $tool->save();
        $this->assertEquals("\0337", $monitor->read(2));

        $tool->restore();
        $this->assertEquals("\0338", $monitor->read(2));
    }

    public function testHideAndShow()
    {
        $tool = ToolTestUtil::getCursorToolInstance();

        $output = new PipedOutputStream();

        $buffer = new \Threaded();
        $monitor = new PipedInputStream($buffer, $output);

        $terminal = $tool->getTerminal();
        $terminal->setOutput($output);

        $tool->hide();
        $this->assertEquals("\033[?25l", $monitor->read(6));

        $tool->show();
        $this->assertEquals("\033[?12;25h", $monitor->read(9));
    }
}
