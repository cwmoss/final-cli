<?php
/*

https://stackoverflow.com/questions/4842424/list-of-ansi-color-escape-sequences
https://www.lihaoyi.com/post/BuildyourownCommandLinewithANSIescapecodes.html

https://stackoverflow.com/questions/15579739/in-an-xterm-can-i-turn-off-bold-or-underline-without-resetting-the-current-colo
disable bold: no support on mac os
*/

namespace slowly\final_cli;

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



    function fg() {
        return self::ansi($this->value);
    }
    function bg() {
        return self::ansi($this->value + 10);
    }
    function bright() {
        return self::ansi($this->value . ';1');
    }
    function short_name() {
        return substr($this->name, 0, 3);
    }
    static function reset() {
        return self::ansi(0);
    }
    static function bold() {
        return self::ansi(1);
    }

    // 21 doesn't work on mac
    static function reset_bold() {
        return self::ansi(0);
    }
    static function underline() {
        return self::ansi(4);
    }
    static function reset_underline() {
        return self::ansi(24);
    }
    static function reversed() {
        return self::ansi(7);
    }
    static function reset_reversed() {
        return self::ansi(27);
    }

    static function blink() {
        return self::ansi(5);
    }
    static function reset_blink() {
        return self::ansi(25);
    }
    static function ansi($code) {
        return "\e[{$code}m";
    }
}
