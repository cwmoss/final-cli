<?php

namespace slowly\final_cli;

use Closure;
use BackedEnum;
use Psr\Container\ContainerInterface;

class app {

    public array $commands;
    public array $caller;
    public string $short = "";
    public string $long = "";

    public function __construct(public string $name = 'a cli app', public string $version = "0.1") {
        $this->get_called_file(debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 3));
        $this->fetch_description();
    }

    public function run(array $argv, Closure|ContainerInterface|null $resolver = null) {
        $parser = new parser($argv);
        // print_r($this);
        $help = $parser->get_switch('h', 'help');
        if ($help && !$parser->command) {
            $this->help();
            return $this;
        }
        try {
            $cmd = $this->match($parser);
            if ($help) {
                $this->help_command($cmd);
            } else {
                [$call, $args] = $cmd->run($parser);
                if (is_string($call) && $resolver) {
                    if ($resolver instanceof ContainerInterface) {
                        $call = $resolver->get($call);
                    } else {
                        $call = $resolver($call);
                    }
                }
                ($call)(...$args);
            }
        } catch (error $e) {
            print "⚠️  problem: " . $e->getMessage() . "\n";
        }
    }

    public function match($parser): command {
        foreach ($this->commands as $cmd) {
            if ($parser->command == $cmd->name) return $cmd;
        }
        throw new error("command not found ({$parser->command})");
    }

    public function version(string $version) {
        $this->version = $version;
        return $this;
    }

    public function add_command(string $class, string $name = "") {
        $this->commands[] = new command($class, $name);
        return $this;
    }

    public function help() {
        terminal::println("<inv><b> " . $this->name . ' </b></inv> version: ' . $this->version);
        terminal::print("\n" . $this->short . "\n" . $this->long);

        terminal::print("\n\nthese commands are available:\n\n");
        $max_len = max(array_map(fn($c) => strlen($c->name), $this->commands));

        foreach ($this->commands as $command) {
            terminal::print("  <b>" . str_pad($command->name, $max_len + 2) . "</b> " . $command->help_short . "\n");
        }
        terminal::println();
        // terminal::println("<blink>now you choose</blink>");
    }

    public function help_command(command $command) {
        terminal::println("<b>" . $this->name . '</b> version: ' . $this->version);
        terminal::println();
        terminal::println("<b>{$command->name}</b> -- " .
            $command->help_short, 2);
        if ($command->help_long) {
            terminal::println();
            terminal::println($command->help_long, 2);
        }
        terminal::println();
        $this->help_command_parameters($command);
        print "\n";
    }

    public function help_command_parameters(command $command) {
        foreach ($command->parameters as $para) {
            $name = $para->pname;
            if ($para->is_positional) {
                if ($para->is_optional) {
                    $name = "[$name]";
                } else {
                    $name = "<$name>";
                }
            }
            terminal::println("<b>$name</b>", 2);
            if ($para->is_enum()) {
                terminal::println($this->help_enum($para->type), 4);
            }
        }
    }
    public function help_enum($type) {
        $parts = array_map(
            fn(BackedEnum $case) => $case->value,
            $type::cases(),
        );
        return join("|", $parts);
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

        $comment = trim(join("\n", array_map(fn($line) => trim($line, "* \n\r\t\v\0"), explode("\n", $comment))));
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
