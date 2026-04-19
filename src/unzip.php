<?php

declare(strict_types=1);

namespace cwmoss\final_cli;

use Exception;
use PharData;

class unzip {

    const supported_types = ["zip", "tgz"];

    public string $temp_dir;

    public function __construct(public file $archive_file, public string $type) {
        if (!in_array($type, self::supported_types)) {
            throw new error("archive type not supported: {$type}.");
        }
        $temp_dir = file::make_tempdir();
        if (!$temp_dir) throw new error("could not create tempdir.");
        $this->temp_dir = $temp_dir;
    }

    public function __destruct() {
        file::rmdir_recursive($this->temp_dir);
    }

    public function extract(): self {
        $code = match ($this->type) {
            "zip" => $this->extract_with_unzip(),
            default => $this->extract_with_tar()
        };
        if ($code !== 0) {
            throw new exception("Could not extract archive.");
        }
        return $this;
    }

    public function extract_with_unzip(): int {
        $cmd = "unzip -q {$this->archive_file->fname} -d {$this->temp_dir}";
        $code = 0;
        $output = [];
        exec($cmd, $output, $code);
        return $code;
    }

    public function extract_with_tar(): int {
        $cmd = "tar -x -z -f {$this->archive_file->fname} -C {$this->temp_dir}";
        $code = 0;
        $output = [];
        exec($cmd, $output, $code);
        return $code;
    }

    public function extract_with_phar(string $dest_dir): bool {
        // @mago-ignore analyzer:possible-method-access-on-null
        $p = new PharData($this->archive_file->fname)->decompress()
            ->extractTo($dest_dir);
        return true;
    }
}
