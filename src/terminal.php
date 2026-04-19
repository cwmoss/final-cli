<?php

declare(strict_types=1);

namespace cwmoss\final_cli;

class terminal {

    /**
     * @var array<string, string> $tags
     */
    static public array $tags = [];

    public function set_output(string $mode = "cli"): static {
        self::set_tags($mode);
        return $this;
    }

    public function sprint(string $text, int $indent = 0): string {
        $text = strtr($text, self::tags());
        if ($indent) {
            // TODO: better with split?
            $ind = str_repeat(" ", $indent);
            $text = $ind . str_replace(\PHP_EOL, (\PHP_EOL . $ind), $text);
        }
        return $text;
    }

    public function sprintln(string $text = "", int $indent = 0): string {
        return $this->sprint($text, $indent) . \PHP_EOL;
    }

    public function print(string $text, int $indent = 0): void {
        print $this->sprint($text, $indent);
    }

    public function println(string $text = "", int $indent = 0): void {
        print $this->sprintln($text, $indent);
    }

    public static function set_tags(string $mode = "cli"): void {
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

    /**
     * @return array<string, string>
     */
    public static function tags(): array {
        if (!self::$tags) self::set_tags();
        return self::$tags;
    }

    public static function ansi(string|int $code, string $text): string {
        $code = (string)$code;
        return "\e[{$code}m{$text}\e[0m";
    }

    public static function bold(string $text): string {
        return self::ansi(1, $text);
    }

    public static function test_colors(): void {
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
                foreach (['bold', 'underline', 'inverse', 'blink'] as $style) {
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
