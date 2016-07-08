[![Build Status](https://api.travis-ci.org/zerustech/terminal.svg)](https://travis-ci.org/zerustech/terminal)

ZerusTech Terminal Component
================================================
The *ZerusTech Terminal Component* is a lightweight library for controlling
cursor, font styles as well as colors in PHP cli applications. It would be
easier to use the [ncurses][3] library, however, we want to keep its
dependencies as small as possible, so that it can be used on a broader range of
systems. 

Besides, we'd like to encapsulate the classes and methods in the most
comfortable way to us and we think it's pretty fun to learn new things, such as
the history of terminal, the ANSI standards, the differences between ANSI, VT100
and other terminals, some tty related commands and topics that we barely touched
before, the terminfo and termcap (which is not supported by this library) as
well as how to parse terminal specifications from a compiled terminfo file.

> This library was inspired by the [hoa/console][1] project, which is great
except that it overlaps [symfony/console][12] in may features and we'd much
prefer the latter for CLI application development. Therefore, we re-implemented
the terminal functionalities that are missing from ``symfony/console`` into this
library.

::: info-box note

This library does not support any Windows Platforms!

:::

Installation
-------------

You can install this component in 2 different ways:

* Install it via Composer
```bash
$ cd <project-root-directory>
$ composer require zerustech/terminal
```

* Use the official Git repository (https://github.com/zerustech/terminal)

Examples
-------------

### Creates a terminal instance ###

The terminal class is an abstract of the virtual terminal, so this is the entry
point where you gain access to variant resources.

```php
<?php

require_once __DIR__.'/vendor/autoload.php';

use ZerusTech\Component\Terminal\Terminal;

$terminal = Terminal::instance();

// Or creates a terminal instance for specific tty and terminal names
// $terminal = Terminal::instance('/dev/ttys001', 'ansi');

```

### Cursor ###

Cursor is controlled by the cursor tool, which can be obtained from the terminal
instance. Here is an example of how to manipulate cursor:

```php
<?php

require_once __DIR__.'/vendor/autoload.php';

use ZerusTech\Component\Terminal\Terminal;

$terminal = Terminal::instance(); // Creates a terminal instance

$cursor = $terminal->getCursor(); // Obtains the cursor tool

$cursor->moveTo(30, 40); // Moves cursor to row 30 and column 40.

$position = $cursor->getPosition(); // ['row' => 30,'col' => 40]

$cursor->move('up'); 
// Moves cursor 1 row upward: (29, 40)
// Valid directions include: 
// up, right, down, left, home and bol (beginning of line)

$cursor->save(); // Saves current cursor position: (29, 40)

$cursor->move('up', 2); // Moves cursor 2 rows upward: (27, 40)

$cursor->restore(); // Restores cursor position: (29, 40)

$cursor->hide(); // Hides cursor

$cursor->show(); // Reveals cursor

// Method chaining is also supported
$terminal
    ->getCursor()                  // Obtains the cursor tool 
        ->moveTo(30, 40)           // Moves to (30, 40)
        ->move('up')               // Moves 1 row upward
        ->save()                   // Saves cursor position
        ->move('up', 2)            // Moves 2 rows upward
        ->restore()                // Restores cursor position
        ->hide()                   // Hides cursor
        ->show()                   // Reveals cursor
    ->terminal();                  // Obtains the terminal instance

```

::: info-box tip

Most methods in this component support method chaining for producing fluent code.

:::


### Screen ###

Screen, font styles and colors are controlled by the screen tool in the terminal
instance. Here is an example of how to use it:

```php
<?php

require_once __DIR__.'/vendor/autoload.php';

use ZerusTech\Component\Terminal\Terminal;

$terminal = Terminal::instance();

$screen = $terminal->getScreen(); // Obtains the screen tool

$screen->clear();         // Clears the full screen.

$screen->clear('bol');    
// Clears characters from current cursor position to the beginning of line.
// Valid parts of the clear() method include: 
//     - all (the default value)
//     - bol (beginning of line)
//     - eol (end of line)
//     - eos (end of screen)

$screen->delete('line'); //  Deletes 1 line upward.

$screen->delete('line', 2); // Deletes 2 lines upward.

$screen->delete('character'); // Deletes 1 character rightward.

$screen->delete('character', 2); // Deletes 2 characters rightward.

$screen->insert('line'); // Inserts 1 line upward.

$screen->insert('line', 2); // Inserts 2 lines upward.

$screen->mode('bold'); // Turns on bold font style.

$screen->mode('bold', false); // Turns off bold font style.

// Valid font styles include:
//     - none (resets all styles)
//     - hide
//     - bold
//     - underscore
//     - blink 
//     - inverse 

$screen->foregroundColor('red'); // Sets foreground color by alias.
// Valid aliases include: 
// black, red, green, yellow, blue, magenta, cyan and white.

$screen->foregroundColor('87ff87'); // Sets foreground color by RGB

$screen->backgroundColor('blue'); // Sets background color by alias.

$screen->backgroundColor('d78700'); // Sets background color by RGB

```

### Terminfo ###

If you want to access the full terminal specifications (all boolean, string and number capabilities),
you can obtain the terminfo object from the Terminal instance:

```php
<?php

require_once __DIR__.'/vendor/autoload.php';

use ZerusTech\Component\Terminal\Terminal;

$terminal = Terminal::instance();

$terminfo = $terminal->getTerminfo();

$string = $terminfo->getString('cursor_home'); 
// Gets the value of a string capability.

$boolean = $terminfo->getBoolean('auto_left_margin'); 
// Gets the value of a boolean capability.

$number = $terminfo->getNumber('max_colors');
// Gets the value of a numberic capability.

```

::: info-box tip 

This component only supports the long capability names in terminfo (the termcap names are not supported).
Refer to [terminfo(5)][5] for the full list of capability names, in alphabet
order, in terminfo.

If you want to know the original order of each capability, refer to file
``include/Caps`` in the latest [ncurses source code][4].

:::

References
----------
* [The term(5) man page][6]
* [ANSI/VT100 terminal control escape sequences][7]
* [The non-canonical mode of terminal][8]
* [The stty(1) man page][9]
* [The infocmp(1) man page][10]
* [The xterm 256 color palette][11]
* [The zerustech/io project][13]
* [The zerustech/threaded project][14]
* [The zerustech/terminal project][15]

[1]:  https://github.com/hoaproject/Console "The hoa/console Project"
[2]:  https://opensource.org/licenses/MIT "The MIT License (MIT)"
[3]:  http://php.net/manual/en/intro.ncurses.php "Ncurses Terminal Screen Control"
[4]:  http://ftp.gnu.org/gnu/ncurses "The Source Code of Ncurses Library"
[5]:  https://www.freebsd.org/cgi/man.cgi?query=terminfo&sektion=5 "The terminfo(5) Man Page"
[6]:  http://linux.die.net/man/5/term "The term(5) Man Page" 
[7]:  http://www.termsys.demon.co.uk/vtansi.htm "ANSI/VT100 Terminal Control Escape Sequences"
[8]:  http://pubs.opengroup.org/onlinepubs/009696799/basedefs/xbd_chap11.html#tag_11_01_06 "Non-Canonical Mode" 
[9]:  http://linux.die.net/man/1/stty "The stty(1) Man Page" 
[10]: http://manpages.sgvulcan.com/infocmp.1m.php "The infocmp(1) Man Page" 
[11]: https://en.wikipedia.org/wiki/File:Xterm_256color_chart.svg "The Xterm 256 Color Palette"
[12]: https://github.com/symfony/console "The Symfony Console Component"
[13]:  https://github.com/zerustech/io "The zerustech/io Project"
[14]:  https://github.com/zerustech/threaded "The zerustech/threaded Project"
[15]:  https://github.com/zerustech/terminal "The zerustech/terminal Project"

License
-------
The *ZerusTech Terminal Component* is published under the [MIT License][2].
