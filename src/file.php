<?php

declare(strict_types=1);

namespace cwmoss\final_cli;

use Exception;

/** @phpstan-consistent-constructor */
class file {
    public string $fname;
    public bool $is_tempfile = false;

    public function __construct(public string $name) {
        $this->fname = self::resolve_filename($name);
    }

    public function __destruct() {
        if ($this->is_tempfile) unlink($this->fname);
    }

    static public function new_tempfile(string $basedir = "", string $prefix = "cli-tempfile"): static {
        $f = tempnam($basedir ?: sys_get_temp_dir(), $prefix);
        if (!$f) throw new Exception("tempfile creation failed.");
        return new static($f)->as_tempfile();
    }

    static public function new_from_response(
        string $contents,
        string $mime,
        ?string $name,
        string $basedir = ""
    ): static {
        if (!$basedir) $basedir = sys_get_temp_dir();
        [$_, $ext] = explode("/", explode(";", $mime)[0]);

        if (!$name) {
            $tname = tempnam($basedir, "cli-response-");
            if (!$tname) throw new Exception("tempfile creation failed. $tname => $name");
            $name = $tname . "." . $ext;
            rename($tname, $name) or throw new Exception("tempfile rename failed. $tname => $name");
        } else {
            $n_ext = pathinfo($name, PATHINFO_EXTENSION);
            if (!$n_ext) $name .= "." . $ext;
        }

        $f = new static($name);
        $f->put_contents($contents);
        return $f;
    }

    public function as_tempfile(): static {
        $this->is_tempfile = true;
        return $this;
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
        return file_get_contents($this->fname) ?: "";
    }

    public function put_contents(mixed $contents): int {
        $ok = file_put_contents($this->fname, $contents);
        if ($ok === false) {
            throw new error("could not write to file: $this->fname");
        }
        return $ok;
    }

    public function get_extension(): string {
        return pathinfo($this->fname, PATHINFO_EXTENSION);
    }

    public function get_size_human(): string {
        return util::human_filesize(filesize($this->fname) ?: 0);
    }

    public function get_size(): int {
        return filesize($this->fname) ?: 0;
    }

    static public function resolve_filename(string $name): string {
        if ($name[0] == '/') return $name;
        if ($name == "-") return "php://stdin";
        if ($name[0] == '~') return util::home_dir() . "/" . ltrim($name, "~");

        $cwd = getcwd();
        if (str_starts_with($name, "..")) return $cwd . "/" . $name;
        if ($name[0] == '.') return $cwd . "/" . $name;
        return $cwd . "/" . $name;
    }

    static public function rmdir_recursive(string $dir): void {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir) ?: [], ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            if (is_dir($path)) {
                self::rmdir_recursive($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    static public function make_tempdir(string $basedir = "", string $prefix = "cli-tempdir"): false|string {
        if (!$basedir) $basedir = sys_get_temp_dir();
        $tempfile = tempnam(sys_get_temp_dir(), $prefix);
        if (!$tempfile) return false;
        // tempnam creates file on disk
        if (file_exists($tempfile)) {
            unlink($tempfile);
        }
        mkdir($tempfile);
        if (is_dir($tempfile)) {
            return $tempfile;
        }
        return false;
    }
}
