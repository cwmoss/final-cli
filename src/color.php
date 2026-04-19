<?php

declare(strict_types=1);
/*

https://stackoverflow.com/questions/4842424/list-of-ansi-color-escape-sequences
https://www.lihaoyi.com/post/BuildyourownCommandLinewithANSIescapecodes.html

https://stackoverflow.com/questions/15579739/in-an-xterm-can-i-turn-off-bold-or-underline-without-resetting-the-current-colo
disable bold: no support on mac os
*/

namespace cwmoss\final_cli;

enum color: string {
    case black = "30";
    case red = "31";
    case green = "32";
    case yellow = "33";
    case blue = "34";
    case magenta = "35";
    case cyan = "36";
    case white = "37";

    #    case reset = "0";

    #    case bold = "1";
    #    case underline = "4";
    #    case reversed = "7";



    function fg(): string {
        return self::ansi($this->value);
    }
    function bg(): string {
        return self::ansi((int)$this->value + 10);
    }
    function bright(): string {
        return self::ansi($this->value . ';1');
    }
    function short_name(): string {
        return substr($this->name, 0, 3);
    }
    static function reset(): string {
        return self::ansi(0);
    }
    static function bold(): string {
        return self::ansi(1);
    }

    // 21 doesn't work on mac
    static function reset_bold(): string {
        return self::ansi(0);
    }

    static function underline(): string {
        return self::ansi(4);
    }
    static function reset_underline(): string {
        return self::ansi(24);
    }
    static function inverse(): string {
        return self::ansi(7);
    }
    static function reset_inverse(): string {
        return self::ansi(27);
    }

    static function blink(): string {
        return self::ansi(5);
    }
    static function reset_blink(): string {
        return self::ansi(25);
    }
    static function ansi(int|string $code): string {
        return "\e[{$code}m";
    }
}
