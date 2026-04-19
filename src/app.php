<?php

declare(strict_types=1);

namespace cwmoss\final_cli;

use Closure;
use Phar;
use Psr\Container\ContainerInterface;
use Throwable;

class app {

    public array $commands;
    public array $command_names;
    public ?command $default_command = null;
    public array $caller;
    public string $short = "";
    public string $long = "";
    public bool $help_when_empty = true;
    public bool $is_single_command = false;
    public bool $debug = false;

    static public int $verbose = 0;

    public function __construct(
        public string $name = 'a-cli-app',
        public string $version = "1.0",
        public ?string $tag = null,
        public int $indent = 2
    ) {
        $this->get_called_file(debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 3));
        $this->fetch_description();
    }

    public function run(array $argv, Closure|ContainerInterface|null $resolver = null) {
        $this->finalize();
        $parser = new parser($argv, $this->command_names);
        if ($this->help_when_empty && $parser->called_with_empty_args()) {
            return $this->help();
        }
        // print_r($this);
        $help = $parser->get_switch('h', 'help');
        if ($help && !$parser->command) {
            return $this->help();
        }
        self::$verbose = (int) $parser->get_switch('v', 'verbose');
        try {
            $cmd = $this->match($parser);
            if ($help) {
                $this->help_command($cmd);
            } else {
                [$call, $args] = $cmd->run($parser);
                if (is_array($call)) {
                    [$cls, $mth] = $call;
                    if ($resolver) {
                        if ($resolver instanceof ContainerInterface) {
                            $obj = $resolver->get($cls);
                        } else {
                            $obj = $resolver($cls, $parser);
                        }
                    } else {
                        $obj = new $cls;
                    }
                    $call = [$obj, $mth];
                }
                ($call)(...$args);
            }
        } catch (error $e) {
            print "⚠️  problem: " . $e->getMessage() . "\n";
            print error::nice_output($e, self::$verbose);
            // if (self::$verbose) print_r($cmd->parameters);
        } catch (Throwable $e) {
            print "⚠️  application problem: " . $e->getMessage() . "\n";
            print error::nice_output($e, true);
        }
    }

    public function finalize() {
        $total = sizeof($this->commands);
        if (!$total) throw new error("missing command definition");
        if ($total == 1) {
            $this->is_single_command = true;
            $this->default_command = $this->commands[0];
            if (!$this->default_command->name) $this->default_command->name = $this->name;
        }
        $this->command_names = array_map(fn(command $cmd) => $cmd->name, $this->commands);
        if ($this->debug) {
            print_r($this);
        }
    }

    public function match($parser): command {
        if (!$parser->command) {
            if ($this->default_command) return $this->default_command;
            throw new error("missing command");
        }
        foreach ($this->commands as $cmd) {
            if ($cmd->match($parser->command)) return $cmd;
        }
        throw new error("command not found ({$parser->command})");
    }

    public function version(string $version) {
        $this->version = $version;
        return $this;
    }

    public function get_version(): string {
        $v = $this->version;
        if (self::is_upgradeable()) {
            $v .= " " . join("/", util::get_platform());
            $v .= " " . util::get_self();
        }
        return $v;
    }

    static public function is_upgradeable(): bool {
        return php_sapi_name() == "micro" || util::is_phar();
    }

    public function add_command(string|object $class, string $name = "", bool $is_default = false, ?string $alias = null) {
        $cmd = new command($class, $name, $alias);
        if ($is_default) {
            if ($this->default_command) {
                throw new error("can't define $cmd->name as custom command, because there is already one definde: $this->default_command->name");
            }
            $this->default_command = $cmd;
        }
        $this->commands[] = $cmd;
        return $this;
    }

    public function add_upgrade_command(string $github_project) {
        if (!self::is_upgradeable()) return $this;
        $up = new upgrade($this->name, $this->version, $github_project, self::get_self());
        return $this->add_command($up, "upgrade");
    }

    public function no_help_when_empty() {
        $this->help_when_empty = false;
        return $this;
    }

    public function debug() {
        $this->debug = true;
        return $this;
    }

    public function tag(): string {
        if ($this->tag) return $this->tag;
        return "<inv><b> " . $this->name . " </b></inv>";
    }
    public function help() {
        $terminal = new terminal();
        $terminal->println($this->tag() . ' ' . $this->get_version());
        $terminal->println();
        $terminal->println($this->short, $this->indent);
        $terminal->println($this->long, $this->indent);
        $terminal->println();
        $terminal->println("these commands are available:");
        $terminal->println();
        $max_len = max(array_map(fn($c) => strlen($c->name), $this->commands));

        foreach ($this->commands as $command) {
            $terminal->println(
                "<b>" . str_pad($command->name, $max_len + 2) . "</b> " . $command->help_short,
                $this->indent
            );
        }
        $terminal->println();
        // terminal::println("<blink>now you choose</blink>");
    }

    public function help_command(command $command) {
        $terminal = new terminal();
        $terminal->println($this->tag() . ' ' . $this->version);
        $terminal->println();
        $terminal->println("<b>{$command->name}</b> -- " .
            $command->help_short, $this->indent);
        if ($command->help_long) {
            $terminal->println();
            $terminal->println($command->help_long, $this->indent);
        }
        $terminal->println();
        $this->help_command_parameters($command);
        $terminal->println();
    }

    public function help_command_parameters(command $command) {
        $terminal = new terminal();
        $pos = $named = [];
        foreach ($command->parameters as $para) {
            if ($para->is_positional) $pos[] = $para;
            else $named[] = $para;
        }
        foreach ([...$pos, ...$named] as $para) {
            $name = "";
            $pname = $para->is_optional ? "[$para->pname]" : "<$para->pname>";
            if ($para->is_positional) {
                $name = $pname;
            } else {
                $name = join(" | ", array_filter([
                    $para->short_option_name ? "-{$para->short_option_name}" : "",
                    $para->long_option_name ? "--{$para->long_option_name}" : ""
                ]));
                if (!$para->is_switch)
                    $name .= "=<{$para->pname}>";
            }
            $terminal->println("<b>$name</b>", $this->indent);
            $et = enum_type::is_enum($para->type);
            if ($et) {
                $terminal->println($this->help_enum($et, $para->type), $this->indent);
            }
            if ($para->description) {
                $terminal->println($para->description, $this->indent);
            }
            $terminal->println();
        }
    }

    public function help_enum(enum_type $et, string $type) {
        return join("|", $et->cases_as_strings($type));
    }

    public function fetch_description() {
        $comment = self::get_first_file_comment_block($this->caller['file']);
        if (!$comment) {
            return;
        }
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

    public static function get_description_from_phpdoc(string $comment) {
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
