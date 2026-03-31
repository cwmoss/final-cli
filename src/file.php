<?php

namespace slowly\final_cli;

class file {
    public string $fname;

    public function __construct(public string $name) {
        $this->fname = self::resolve_filename($name);
    }

    static public function new_from_response(string $contents, string $mime, ?string $name, ?string $basedir = null): static {
        if (!$name) {
            $name = tempnam($basedir ?: sys_get_temp_dir(), "cli-response-");
        }
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        if (!$ext) {
            [$_, $ext] = explode("/", explode(";", $mime)[0]);
            $name .= "." . $ext;
        }
        $f = new self($name);
        $f->put_contents($contents);
        return  $f;
    }

    public function must_be_readable(): static {
        if (!file_exists($this->fname)) {
            throw new error("file does not exist: $this->name");
        }
        if (!is_readable($this->fname)) {
            throw new error("file is not readable: $this->name");
        }
        return $this;
    }

    public function get_contents(): string {
        return file_get_contents($this->fname);
    }

    public function put_contents(mixed $contents): string {
        return file_put_contents($this->fname, $contents);
    }

    public function get_extension(): string {
        return pathinfo($this->fname, PATHINFO_EXTENSION);
    }

    public function get_size(): int {
        return filesize($this->fname) ?: 0;
    }

    static public function resolve_filename(string $name) {
        if ($name[0] == '/') return $name;
        if ($name[0] == '~') return util::home_dir() . "/" . ltrim($name, "~");

        $cwd = getcwd();
        if (str_starts_with($name, "..")) return $cwd . "/" . $name;
        if ($name[0] == '.') return $cwd . "/" . $name;
        return $cwd . "/" . $name;
    }
}
