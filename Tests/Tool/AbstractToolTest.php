<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For full copyright and license information, please view the LICENSE file that
 * was distributed with this source code.
 */

namespace ZerusTech\Component\Terminal\Tests\Tool;

use ZerusTech\Component\Terminal\Terminal;
use ZerusTech\Component\Terminal\Tool\AbstractTool;
use ZerusTech\Component\Terminal\Tests\TerminalTestUtil;
use ZerusTech\Component\IO\Stream\Output\OutputStreamInterface;

/**
 * Test case for abstract tool.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class AbstractToolTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $terminal = TerminalTestUtil::getTerminalInstance();

        $tool = $this->getMockForAbstractClass(AbstractTool::class, [$terminal]);

        $this->assertSame($terminal, $tool->getTerminal());
    }

    public function testSend()
    {
        $reflection = new \ReflectionClass(AbstractTool::class);
        $method = $reflection->getMethod('send');
        $method->setAccessible(true);

        $terminal = TerminalTestUtil::getTerminalInstance();

        $output = $this->getMockForAbstractClass(OutputStreamInterface::class);

        $output
            ->expects($this->once())
            ->method('write')
            ->with('hello');

        $terminal->setOutput($output);

        $tool = $this->getMockForAbstractClass(AbstractTool::class, [$terminal]);

        $method->invokeArgs($tool, ['hello']);
    }

    public function testGetters()
    {

        $reflection = new \ReflectionClass(AbstractTool::class);
        $getString = $reflection->getMethod('getString');
        $getNumber = $reflection->getMethod('getNumber');
        $getBoolean = $reflection->getMethod('getBoolean');

        $getString->setAccessible(true);
        $getNumber->setAccessible(true);
        $getBoolean->setAccessible(true);

        $terminfo = $this
            ->getMockBuilder(Terminfo::class)
            ->setMethods(['getString', 'getNumber', 'getBoolean'])
            ->getMock();

        $terminfo
            ->expects($this->once())
            ->method('getString')
            ->with($this->equalTo('string name'))
            ->will($this->returnValue('string value'));

        $terminfo
            ->expects($this->once())
            ->method('getNumber')
            ->with($this->equalTo('number name'))
            ->will($this->returnValue('number value'));

        $terminfo
            ->expects($this->once())
            ->method('getBoolean')
            ->with($this->equalTo('boolean name'))
            ->will($this->returnValue('boolean value'));

        $terminal = $this
            ->getMockBuilder(Terminal::class)
            ->setMethods(['getTerminfo'])
            ->disableOriginalConstructor()
            ->getMock();

        $terminal
            ->expects($this->any())
            ->method('getTerminfo')
            ->will($this->returnValue($terminfo));

        $tool = $this->getMockForAbstractClass(AbstractTool::class, [$terminal]);

        $this->assertEquals('string value', $getString->invokeArgs($tool, ['string name']));
        $this->assertEquals('number value', $getNumber->invokeArgs($tool, ['number name']));
        $this->assertEquals('boolean value', $getBoolean->invokeArgs($tool, ['boolean name']));
    }
}
