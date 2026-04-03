<?php

namespace cwmoss\final_cli;

/*
https://gist.github.com/mindplay-dk/082458088988e32256a827f9b7491e17
*/

use ReflectionParameter;

class parameter {

    public string $name;
    public string $pname;
    public ?string $short_option_name = null;
    public ?string $long_option_name = null;
    public ?string $type;
    public ?string $description = null;
    public int $position;
    public mixed $default;

    public bool $is_variadic;
    public bool $is_optional;
    public bool $is_positional;
    public bool $is_switch;
    public bool $is_nullable;

    public ?int $min = null;
    public ?int $max = null;

    public function __construct(ReflectionParameter $parameter) {
        $this->fetch($parameter);
    }

    public function fetch(ReflectionParameter $parameter) {
        if ($parameter->isDefaultValueAvailable()) $this->default = $parameter->getDefaultValue();
        else $this->default = null;

        $this->name = $parameter->getName();
        $this->pname = self::get_parameter_name($this->name);
        $this->position = $parameter->getPosition();
        $this->type = $parameter->getType()?->getName();

        $this->is_optional = $parameter->isOptional();
        $this->is_variadic = $parameter->isVariadic();
        $this->is_positional = $this->name[0] == '_';
        $this->is_switch = $this->type == 'bool' || is_bool($this->default);
        $this->is_nullable = $parameter->allowsNull();

        if (!$this->is_positional) {
            [$short, $long] = explode("--", $this->pname, 2) + [1 => null];
            if ($long) {
                $this->short_option_name = $short;
                $this->long_option_name = $long;
                $this->pname = $long;
            } else {
                if (strlen($this->pname) == 1) {
                    $this->short_option_name = $this->pname;
                } else {
                    $this->long_option_name = $this->pname;
                }
            }
        }
        $attr = $this->get_attributes($parameter);
        if ($attr) {
            if ($attr->short_option || $attr->long_option) {
                $this->short_option_name = $attr->short_option;
                $this->long_option_name = $attr->long_option;
                $this->is_positional = false;
            } else {
                // only parameter name means, it's positional
                // TODO: check w/ description?
                $this->short_option_name = null;
                $this->long_option_name = null;
                $this->is_positional = true;
            }
            if ($attr->parameter_name) $this->pname = $attr->parameter_name;
            $this->description = $attr->description;
        }

        # if (!$this->is_optional) $this->default = $parameter->getDefaultValue();
    }

    public static function get_parameter_name($name) {
        $name = ltrim($name, '_');
        $name = strtr($name, '_', '-');
        return $name;
    }

    public function get_attributes($parameter): ?cli {
        $attrs = $parameter->getAttributes();
        foreach ($attrs as $attr) {
            if (!str_starts_with($attr->getName(), 'cwmoss\\final_cli')) continue;
            // print $attr->getName();
            $a = $attr->newInstance();
            return $a;
            // $a->set($this);
        }
        return null;
    }
}
