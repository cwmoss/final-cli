<?php

namespace slowly\final_cli;

class util {

    static function home_dir(): string {
        $result = $_SERVER['HOME'] ?? getenv("HOME") ?? "";

        if ($result === "" && function_exists('exec')) {
            if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
                $result = exec("echo %userprofile%");
            } else {
                $result = exec("echo ~");
            }
        }

        return (string) $result;
    }

    static function editor(): string {
        return $_SERVER['EDITOR'] ?? getenv("EDITOR") ?: "vim";
    }

    static function call_ed(string $filename) {
        $cmd = sprintf("%s %s > `tty`", self::editor(), $filename);
        system($cmd);
    }
}
