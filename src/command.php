<?php

namespace slowly\final_cli;

use PHPUnit\Framework\MockObject\Rule\Parameters;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionFunctionAbstract;
use Closure;

class command {

    public array $flags = [];
    public array $opts = [];
    public array $args = [];

    /**
     * @param array<parameter> $parameters
     */
    public array $parameters = [];

    public string $help_short = "";
    public string $help_long = "";
    public Closure|string $call;

    public function __construct(public string|Closure $command, public ?string $name = null, public ?string $alias = null) {
        if (!$name && is_string($command)) {
            $n = explode('\\', $command);
            $this->name = array_pop($n);
        }
        $this->inspect($command);
    }

    public function match(string $name) {
        return $this->name == $name || ($this->alias && $this->alias == $name);
    }

    public function run(parser $parser): array {
        $args = [];
        foreach ($this->parameters as $parm) {
            $name = $parm->name;
            $pname = $parm->pname;
            if ($parm->is_switch) {
                $val = $parser->get_switch($parm->long_option_name, $parm->short_option_name);
            } elseif ($parm->is_positional) {
                if ($parm->type == "array") $val = $parser->args;
                else {
                    $val = array_shift($parser->args);
                    if ($val === null && !$parm->is_optional) {
                        throw new error("missing argument {$pname}");
                    } elseif ($val === null) {
                        $val = $parm->default;
                    } else {
                        $val = self::convert_value($parm, $val);
                    }
                }
            } else {
                $val = $parser->get_opt($parm->long_option_name, $parm->short_option_name);
                if (is_null($val)) {
                    if ($parm->is_optional) {
                        $val = $parm->default;
                    } elseif ($parm->is_nullable) {
                        $val = null;
                    } else {
                        throw new error("missing option {$pname}");
                    }
                } else {
                    $val = self::convert_value($parm, $val);
                }
            }
            $args[] = $val;
        }
        // ($this->call)(...$args);
        return [$this->call, $args];
    }

    static public function convert_value(parameter $parm, string $val): mixed {
        $type = $parm->type;
        if ($parm->is_enum()) {
            return $type::from($val);
        }
        return match ($type) {
            null, "string" => $val,
            "int" => intval($val),
            "float" => floatval($val),
            default => new $type($val)
        };
    }
    public function name() {
        return $this->name;
    }
    // TODO: support callable as $class
    public function inspect(string|Closure $class, $method = '__invoke') {
        if (is_string($class)) {
            if (function_exists($class)) {
                $this->call = $class(...);
                $rflc = new ReflectionFunction($class);
            } else {
                // class name
                $this->call = $class;
                // $this->call = fn (...$args) => (new $class)->$method(...$args);
                $rflc = new ReflectionMethod($class, $method);
            }
        } else {
            // closure
            $this->call = $class;
            $rflc = new ReflectionFunction($class);
        }

        $parameters = $rflc->getParameters();

        $tmp = [];
        foreach ($parameters as $parameter) {
            $p = new parameter($parameter);
            $tmp[] = $p;
        }
        $this->parameters = $tmp;

        $this->get_doc($rflc);
    }

    public function get_doc(ReflectionFunctionAbstract $rflc) {
        $comment = $rflc->getDocComment();

        [$this->help_short, $this->help_long] = app::get_description_from_phpdoc($comment);
    }
}
