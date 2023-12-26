<?php

namespace slowly\final_cli;

use Attribute;

#[Attribute(Attribute::TARGET_ALL)]
class alias {
    function __construct(private string $parameter) {
        // echo "Running " . __METHOD__,
        //  " arg: " . $this->parameter . PHP_EOL;
    }

    public function set($obj) {
        $obj->alias = $this->parameter;
    }
}
