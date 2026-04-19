<?php

declare(strict_types=1);

namespace cwmoss\final_cli;

use PharData;

class unzip {


    public function __construct(public string $archive_file) {
    }

    public function extract() {
    }

    public function extract_with_phar(string $dest_dir): bool {
        $p = new PharData($this->archive_file)->decompress()
            ->extractTo($dest_dir);
        return true;
    }
}
