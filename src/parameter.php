<?php

namespace slowly\final_cli;

/*
https://gist.github.com/mindplay-dk/082458088988e32256a827f9b7491e17
*/

use BackedEnum;
use ReflectionParameter;

class parameter {

    public string $name;
    public string $pname;
    public int $position;
    public ?string $type;
    public mixed $default;
    public bool $is_optional;
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
        if ($parameter->isDefaultValueAvailable()) $this->default = $parameter->getDefaultValue();
        else $this->default = null;
        $this->attributes = $attr = $this->get_attributes($parameter);
        $this->alias = $attr?->alias;
        $this->name = $parameter->getName();
        $this->pname = self::get_parameter_name($this->name);
        $this->position = $parameter->getPosition();
        $this->type = $parameter->getType()?->getName();

        $this->is_optional = $parameter->isOptional();

        $this->variadic = $parameter->isVariadic();
        $this->is_positional = $this->name[0] == '_';
        $this->is_switch = $this->type == 'bool' || is_bool($this->default);
        $this->is_nullable = $parameter->allowsNull();
        # if (!$this->is_optional) $this->default = $parameter->getDefaultValue();
    }

    public function is_enum(): bool {
        return is_subclass_of($this->type, BackedEnum::class);
    }

    public static function get_parameter_name($name) {
        $name = ltrim($name, '_');
        $name = strtr($name, '_', '-');
        return $name;
    }
    public function get_attributes($parameter): ?alias {
        $attrs = $parameter->getAttributes();
        foreach ($attrs as $attr) {
            if (!str_starts_with($attr->getName(), 'slowly\\final_cli')) continue;
            // print $attr->getName();
            $a = $attr->newInstance();
            return $a;
            // $a->set($this);
        }
        return null;
    }
}
