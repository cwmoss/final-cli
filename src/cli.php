<?php

namespace slowly\final_cli;

use Attribute;

#[Attribute(Attribute::TARGET_ALL)]
class cli {
    public ?string $short_option = null;
    public ?string $long_option = null;
    public ?string $parameter_name = null;

    /**
     * @param null|string $definition The name of the argument. If not provided, the name of the associated parameter or property will be used.
     * @param null|string $description A short description explaining what this argument does.
     */
    public function __construct(
        public ?string $definition = null,
        public ?string $description = null,
    ) {
        $this->parse_definition();
    }

    public function parse_definition() {
        if (!$this->definition) return;
        foreach (explode(" ", $this->definition) as $def) {
            if (!$def) continue;
            if (str_starts_with($def, "--")) {
                $this->long_option = ltrim($def, "-");
            } elseif (str_starts_with($def, "-")) {
                $this->short_option = ltrim($def, "-");
            } else {
                $this->parameter_name = $def;
            }
        }
    }
}
