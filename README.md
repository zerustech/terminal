# ZerusTech Terminal Component
The *ZerusTech Terminal Component* is a lightweight library for controlling
cursor, font styles as well as colors in PHP cli applications. It would be
easier to use the ncurses library, however, we want to keep its
dependencies as small as possible, so that it can be used on a broader range of
systems. 

Besides, we'd like to encapsulate the classes and methods in the most
comfortable way to us and we think it's pretty fun to learn new things, such as
the history of terminal, the ANSI standards, the differences between ANSI, VT100
and other terminals, some tty related commands and topics that we barely touched
before, the terminfo and termcap (which is not supported by this library) as
well as how to parse terminal specifications from a compiled terminfo file.

> This library was inspired by the hoa/console project, which is great
except that it overlaps symfony/console in may features and we'd much
prefer the latter for CLI application development. Therefore, we re-implemented
the terminal functionalities that are missing from ``symfony/console`` into this
library.

# Project Moved to GitLab
This project has been moved to [GitLab][1].

References
* [zerustech/terminal][1]

[1]: https://gitlab.com/zerustech/terminal "zerustech/terminal"
