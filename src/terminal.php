<?php

namespace slowly\final_cli;

class terminal {

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
