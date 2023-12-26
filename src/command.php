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
    public array $aliases = [];

    public array $tmp;
    public string $help_short = "";
    public string $help_long = "";
    public Closure $call;

    public function __construct(public string $command, public ?string $name = null) {
        if (!$name) {
            $n = explode('\\', $command);
            $this->name = array_pop($n);
        }
        $this->inspect($command);
    }
    public function run(parser $parser) {
        $args = [];
        foreach ($this->tmp as $parm) {
            $name = $parm->name;
            $pname = parameter::get_parameter_name($name);
            if ($parm->is_switch) {
                $val = $parser->get_switch($pname);
            } elseif ($parm->is_positional) {
                $val = $parser->args;
            } else {
                $val = $parser->get_opt($pname);
                if (is_null($val)) {
                    if ($parm->default) {
                        $val = $parm->default;
                    } elseif ($parm->is_nullable) {
                        $val = null;
                    } else {
                        throw new error("missing argument {$pname}");
                    }
                }
            }
            $args[] = $val;
        }
        ($this->call)(...$args);
    }
    public function name() {
        return $this->name;
    }
    public function inspect($class, $method = '__invoke') {
        if (function_exists($class)) {
            $this->call = $class(...);
            $rflc = new ReflectionFunction($class);
        } else {
            $this->call = fn (...$args) => (new $class)->$method(...$args);
            $rflc = new ReflectionMethod($class, $method);
        }

        $parameters = $rflc->getParameters();

        $tmp = [];
        foreach ($parameters as $parameter) {
            $p = new parameter($parameter);
            $tmp[] = $p;
        }
        $this->tmp = $tmp;

        $this->get_doc($rflc);
    }

    public function get_doc(ReflectionFunctionAbstract $rflc) {
        $comment = $rflc->getDocComment();

        [$this->help_short, $this->help_long] = app::get_description_from_phpdoc($comment);
    }
}
