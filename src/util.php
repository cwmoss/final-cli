<?php

namespace cwmoss\final_cli;

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

    static function human_filesize($bytes, $decimals = 2) {
        $factor = floor((strlen($bytes) - 1) / 3);
        if ($factor > 0) $sz = 'KMGT';
        return sprintf("%.{$decimals}f %sB", $bytes / pow(1024, $factor), $sz[$factor - 1] ?? "");
    }
}
