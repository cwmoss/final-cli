<?php

namespace slowly\final_cli;

class app {

    public array $commands;
    public array $caller;
    public string $short = "";
    public string $long = "";

    public function __construct(public string $name = 'a cli app', public string $version = "0.1") {
        $this->get_called_file(debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 3));
        $this->fetch_description();
    }

    public static function new(?string $name) {
        $cli = new self($name);
        return $cli;
    }

    public function version(string $version) {
        $this->version = $version;
        return $this;
    }

    public function add_command(string $class) {
        $this->commands[] = new command($class);
        return $this;
    }

    public function help() {
    }

    public function fetch_description() {
        $comment = self::get_first_file_comment_block($this->caller['file']);
        [$this->short, $this->long] = self::get_description_from_phpdoc($comment);
    }

    public function get_called_file(array $trace) {
        foreach ($trace as $entry) {
            if ($entry['file'] != __FILE__) {
                $this->caller = $entry;
                return;
            }
        }
    }

    public static function get_description_from_phpdoc($comment) {
        $comment = substr($comment, 3, -2);
        # $lines = array_map(fn ($line) => ltrim("\r\t", $line), explode("\n", $comment));

        $comment = trim(join("\n", array_map(fn ($line) => trim($line, "* \n\r\t\v\0"), explode("\n", $comment))));
        [$short, $long] = explode("\n\n", $comment, 2) + [1 => ""];
        // if first line ends with a . (dot) then it is the short description
        $shortlines = explode("\n", $short);
        if (str_ends_with($shortlines[0], '.')) {
            $short = rtrim($shortlines[0], '.');
            array_shift($shortlines);
            $long = trim(join("\n", $shortlines) . "\n" . $long);
        }
        return [$short, $long];
    }

    public static function get_first_file_comment_block($file_name) {
        $Comments = array_filter(
            token_get_all(file_get_contents($file_name)),
            function ($entry) {
                return $entry[0] == T_DOC_COMMENT;
            }
        );

        $fileComment = array_shift($Comments);
        return $fileComment[1];
    }
}
