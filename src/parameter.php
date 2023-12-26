<?php

namespace slowly\final_cli;

/*
https://gist.github.com/mindplay-dk/082458088988e32256a827f9b7491e17
*/

use ReflectionParameter;

use function PHPUnit\Framework\stringStartsWith;

class parameter {

    public string $name;
    public int $position;
    public ?string $type;
    public mixed $default;
    public bool $optional;
    public $attributes;
    public bool $variadic;
    public ?string $alias = null;
    public ?int $min = null;
    public ?int $max = null;
    public bool $is_positional;
    public bool $is_switch;
    public bool $is_nullable;

    public function __construct(ReflectionParameter $parameter) {
        $this->fetch($parameter);
    }

    public function fetch(ReflectionParameter $parameter) {
        $this->name = $parameter->getName();
        $this->position = $parameter->getPosition();
        $this->type = (string) $parameter->getType();
        $this->optional = $parameter->isOptional();
        $this->attributes = $this->get_attributes($parameter);
        $this->variadic = $parameter->isVariadic();
        $this->is_positional = $this->name[0] == '_';
        $this->is_switch = $this->type == 'bool';
        if ($parameter->isDefaultValueAvailable()) $this->default = $parameter->getDefaultValue();
        else $this->default = null;
        $this->is_nullable = $parameter->allowsNull();
        # if (!$this->optional) $this->default = $parameter->getDefaultValue();
    }

    public static function get_parameter_name($name) {
        $name = ltrim($name, '_');
        $name = strtr($name, '_', '-');
        return $name;
    }
    public function get_attributes($parameter) {
        $attrs = $parameter->getAttributes();
        foreach ($attrs as $attr) {
            if (!str_starts_with($attr->getName(), 'slowly\\final_cli')) continue;
            // print $attr->getName();
            $a = $attr->newInstance();
            $a->set($this);
        }
    }
}
