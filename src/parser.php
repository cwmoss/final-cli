<?php

declare(strict_types=1);

namespace cwmoss\final_cli;

class parser {

    // $ miggi init
    public string $command = "";

    // $ miggi migrate --yes
    public array $switches = [];

    // $ miggi --dir=db/migrations
    public array $opts = [];

    // $ miggi new create_todos_tables
    public array $args = [];

    // bin/miggi.php
    public string $script = "";

    public function __construct(array $args, array $commands = []) {
        $this->script = array_shift($args);
        $this->parse($args, $commands);
    }

    public function called_with_empty_args(): bool {
        return (!$this->command && !$this->args && !$this->opts && !$this->switches);
    }

    public function parse(array $args, array $commands = []) {
        $only_args_left = false;
        foreach ($args as $token) {
            if ($only_args_left) {
                $this->args[] = $token;
                continue;
            }

            if ($token === "--") {
                $only_args_left = true;
                continue;
            }

            if ($token === "-") {
                $this->args[] = $token;
                continue;
            }

            if (preg_match('/^--([^=]+)=(.*)/', $token, $match)) {
                $this->opts[$match[1]] = $match[2];
            } elseif (preg_match('/^--([^=]+)/', $token, $match)) {
                $this->switches[$match[1]] = true;
            } elseif (preg_match('/^-([^=])=(.*)/', $token, $match)) {
                $this->opts[$match[1]] = $match[2];
            } elseif (preg_match('/^-([^=])/', $token, $match)) {
                $this->switches[$match[1]] = true;
            } else {
                $this->args[] = $token;
            }
        }
        if (count($commands) === 1) {
            $this->command = $commands[0];
        } else {
            if ($this->args) {
                $this->command = array_shift($this->args);
            }
        }
    }

    function get_opt(...$tests) {
        // print_r($tests);
        foreach ($tests as $t) {
            if ($t === null) continue;
            if (isset($this->opts[$t])) return $this->opts[$t];
        }
        return null;
    }

    function get_switch(...$tests) {
        // print_r($tests);
        foreach ($tests as $t) {
            if ($t === null) continue;
            if (isset($this->switches[$t])) return true;
        }
        return false;
    }
}
