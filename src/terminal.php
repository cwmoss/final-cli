<?php

namespace cwmoss\final_cli;

class terminal {

    static public array $tags = [];

    public function set_output($mode = "cli") {
        self::set_tags($mode);
        return $this;
    }

    public function sprint($text, $indent = 0) {
        $text = strtr($text, self::tags());
        if ($indent) {
            // TODO: better with split?
            $ind = str_repeat(" ", $indent);
            $text = $ind . str_replace(\PHP_EOL, (\PHP_EOL . $ind), $text);
        }
        return $text;
    }

    public function sprintln($text = "", $indent = 0) {
        return $this->sprint($text, $indent) . \PHP_EOL;
    }

    public function print($text, $indent = 0) {
        print $this->sprint($text, $indent);
    }

    public function println($text = "", $indent = 0) {
        print $this->sprintln($text, $indent);
    }

    public static function set_tags($mode = "cli") {
        if ($mode == "cli") {
            self::$tags = [
                '<b>' => color::bold(),
                '</b>' => color::reset(),
                '<u>' => color::underline(),
                '</u>' => color::reset_underline(),
                '<blink>' => color::blink(),
                '</blink>' => color::reset_blink(),
                '<inv>' => color::inverse(),
                '</inv>' => color::reset_inverse(),
                '<green>' => color::green->fg(),
                '</green>' => color::reset(),
                '<red>' => color::red->fg(),
                '</red>' => color::reset(),
                '<pre>' => '',
                '</pre>' => '',
                '<ok>' => color::bold() . color::green->bg() . color::white->fg() . ' ',
                '</ok>' => ' ' . color::reset()
            ];
            return;
        }
        self::$tags = [
            '<b>' => '<strong>',
            '</b>' => '</strong>',
            '<u>' => '<em>',
            '</u>' => '</em>',
            '<blink>' => '<mark>',
            '</blink>' => '</mark>',
            '<inv>' => '<span class="inv">',
            '</inv>' => '</span>',
            '<green>' => '<span class="green">',
            '</green>' => '</span>',
            '<red>' => '<span class="red">',
            '</red>' => '</span>',
            '<pre>' => '<pre>',
            '</pre>' => '</pre>',
            '<ok>' => '<span class="ok"> ',
            '</ok>' => ' </span>',
        ];
        // html mode
    }

    public static function tags() {
        if (!self::$tags) self::set_tags();
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
