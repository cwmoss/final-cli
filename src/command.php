<?php

namespace slowly\final_cli;

use ReflectionMethod;
use ReflectionFunctionAbstract;

class command {

    public array $flags = [];
    public array $opts = [];
    public array $args = [];
    public array $aliases = [];

    public array $tmp;
    public string $help_short = "";
    public string $help_long = "";

    public function __construct(public string $command, public ?string $name = null) {
        $this->inspect($command);
    }
    public function name() {
        return $this->name;
    }
    public function inspect($class, $method = '__invoke') {
        $rflc = new ReflectionMethod($class, $method);
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
