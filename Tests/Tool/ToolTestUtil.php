<?php

namespace ZerusTech\Component\Terminal\Tests\Tool;

use ZerusTech\Component\Terminal\Tests\TerminalTestUtil;
use ZerusTech\Component\Terminal\Tool\CursorTool;
use ZerusTech\Component\Terminal\Tool\ScreenTool;

class ToolTestUtil
{
    public static function getCursorToolInstance()
    {
        $terminal = TerminalTestUtil::getTerminalInstance();
        return new CursorTool($terminal);
    }

    public static function getScreenToolInstance()
    {
        $terminal = TerminalTestUtil::getTerminalInstance();
        return new ScreenTool($terminal);
    }
}
