<?php

declare(strict_types=1);

namespace cwmoss\final_cli;

use RuntimeException;

class input {

    static public function readline(string $prompt = ''): string {
        $prompt && print $prompt;
        // $terminal_device = '/dev/tty';
        $terminal_device = 'php://stdin';
        // $terminal_device = STDIN;
        $h = fopen($terminal_device, 'r');
        if ($h === false) {
            throw new RuntimeException("Failed to open terminal device $terminal_device");
        }
        $line = fgets($h);
        if ($line === false) {
            throw new RuntimeException("Failed to read terminal device $terminal_device");
        }
        $line = rtrim($line, "\r\n");
        fclose($h);
        return $line;
    }
}
