<?php

declare(strict_types=1);

namespace cwmoss\final_cli;

use Phar;

class util {

    /**
     * @return string[]
     */
    static function get_platform(): array {
        $os = match (PHP_OS_FAMILY) {
            'Linux' => 'linux',
            'Darwin' => 'macos',
            'Windows' => 'win',
            default => throw new \Exception("Unsupported OS: " . PHP_OS_FAMILY)
        };
        $arch = php_uname('m');
        if (str_contains($arch, 'arm64') || str_contains($arch, 'aarch64')) {
            $arch = 'aarch64';
        } else {
            $arch = 'x86_64';
        }
        return [$os, $arch];
    }

    // this programm is running as a phar archive
    static public function is_phar(): bool {
        return class_exists(Phar::class) && Phar::running(false) !== "";
    }

    // this programm is running as a normal hosted phar archive
    static public function is_hosted_phar(): bool {
        return php_sapi_name() != "micro" && self::is_phar();
    }

    static public function get_self(): string {
        if (self::is_phar()) return Phar::running(false);
        return $_SERVER["_"];
    }

    static function home_dir(): string {
        $result = $_SERVER['HOME'] ?? ((string)getenv("HOME"));

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

    static function call_ed(string $filename): void {
        $cmd = sprintf("%s %s > `tty`", self::editor(), $filename);
        system($cmd);
    }

    static function human_filesize(int $bytes, int $decimals = 2): string {
        $factor = (int)floor((strlen((string)$bytes) - 1) / 3);
        $size = "";
        if ($factor > 0 && $factor < 4) $size = 'KMGT'[$factor - 1];
        return sprintf("%.{$decimals}f %sB", $bytes / pow(1024, $factor), $size);
    }

    static function load_dot_env_file(string $env_file, bool $silent = true): bool {

        if (!is_readable($env_file)) {
            if ($silent) return false;
            throw new \RuntimeException(sprintf('%s .env file is not readable', $env_file));
        }

        $lines = file($env_file) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) continue;
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
        return true;
    }
}
