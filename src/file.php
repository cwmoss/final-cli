<?php

namespace slowly\final_cli;

class file {

    public function __construct(public string $name) {
    }

    static public function new_from_response(string $contents, string $mime, ?string $name, ?string $basedir = null): static {
        if (!$name) {
            $name = tempnam($basedir ?: sys_get_temp_dir(), "cli-response-");
        }
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        if (!$ext) {
            [$_, $ext] = explode("/", $mime);
            $name .= "." . $ext;
        }
        file_put_contents($name, $contents);
        return new self($name);
    }

    public function must_be_readable(): static {
        if (!file_exists($this->name)) {
            throw new error("file does not exist: $this->name");
        }
        if (!is_readable($this->name)) {
            throw new error("file is not readable: $this->name");
        }
        return $this;
    }

    public function get_contents(): string {
        return file_get_contents($this->name);
    }

    public function get_extension(): string {
        return pathinfo($this->name, PATHINFO_EXTENSION);
    }

    public function get_size(): int {
        return filesize($this->name) ?: 0;
    }
}
