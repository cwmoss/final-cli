<?php

namespace slowly\final_cli;

/*
https://gist.github.com/mindplay-dk/082458088988e32256a827f9b7491e17
*/

use ReflectionParameter;

class parameter {

    public string $name;
    public int $position;
    public ?string $type;
    public mixed $default;
    public bool $optional;
    public $attributes;
    public bool $variadic;

    public function __construct(ReflectionParameter $parameter) {
        $this->fetch($parameter);
    }

    public function fetch(ReflectionParameter $parameter) {
        $this->name = $parameter->getName();
        $this->position = $parameter->getPosition();
        $this->type = (string) $parameter->getType();
        $this->optional = $parameter->isOptional();
        $this->attributes = $parameter->getAttributes();
        $this->variadic = $parameter->isVariadic();
        if ($parameter->isDefaultValueAvailable()) $this->default = $parameter->getDefaultValue();
        # if (!$this->optional) $this->default = $parameter->getDefaultValue();
    }
}
