<?php

namespace slowly\final_cli;

class terminal {

    static public array $tags = [];

    public static function sprint($text, $indent = 0) {
        $text = strtr($text, self::tags());
        if ($indent) {
            $text = str_replace(\PHP_EOL, (\PHP_EOL . str_repeat(" ", $indent)), $text);
        }
        return $text;
    }

    public static function sprintln($text, $indent = 0) {
        return self::sprint($text . \PHP_EOL, $indent);
    }

    public static function print($text, $indent = 0) {
        print self::sprint($text, $indent);
    }

    public static function println($text, $indent = 0) {
        print self::sprintln($text, $indent);
    }

    public static function tags() {
        self::$tags = [
            '<b>' => color::bold(),
            '</b>' => color::reset(),
            '<u>' => color::underline(),
            '</u>' => color::reset_underline(),
            '<blink>' => color::blink(),
            '</blink>' => color::reset_blink(),
        ];
        return self::$tags;
    }

    public static function ansi($code, $text) {
        return "\e[{$code}m{$text}\e[0m";
    }

    public static function bold($text) {
        return self::ansi(1, $text);
    }

    public static function test_colors() {
        foreach (color::cases() as $case) {
            print $case->fg() . " " . $case->short_name() . " ";
            //print $case->fg(true) . " " . $case->short_name() . " ";
        }
        print color::reset();
        print "\n";

        foreach (color::cases() as $fg) {
            foreach (color::cases() as $bg) {
                print $fg->fg() . $bg->bg() . " " . $fg->short_name() . "/" . $bg->short_name() . " ";
            }
            print color::reset();
            print "\n";
        }
        print color::reset();
        print "\n";

        foreach (color::cases() as $fg) {
            foreach (color::cases() as $bg) {
                foreach (['bold', 'underline', 'reversed', 'blink'] as $style) {
                    print $fg->fg() . $bg->bg() . color::$style() . " " .
                        $fg->short_name() . "/" . $bg->short_name() . " " . color::reset();
                }
                print color::reset();
                print "\n";
            }
        }
        print color::reset();
        print "\n";
    }
}
