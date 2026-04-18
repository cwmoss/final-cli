<?php

namespace cwmoss\final_cli;

/*

    self upgrade is possible for 
        - single binary distribution
        - phar file distribution
        
    currently only github projects are supported
    
    conventions:
        - downloads are found as assets of releases
        - version tags like: 1.2.3, v1.2.3, third number optional
    
    nameing:
        - zipped binaries: {program-name}-{os}-{arch}.{tar.gz|zip}
            windows ends with .zip
        - phar: {program-name}.phar
        - os: linux, macos, win
        - arch: x86_64, aarch64
    
    example: 
        - diary-linux-x86_64.tar.gz
        - diary-macos-aarch64.tar.gz
        - diary-win-x86_64.zip
        - diary.phar

*/

class upgrade {

    public function __construct(
        public string $current_version,
        public string $github_project = "",
        public string $destination = ""
    ) {
    }

    /**
     * upgrade from github to latest version.
     */
    public function __invoke() {
        $term = new terminal;
        $term->println("checking for new version");
        $new_version = $this->check_version();
        if (!$new_version) {
            $term->println("You're already on the latest version: <b>{$this->current_version}</b>");
            return;
        }
    }

    public function check_version(): false|array {
        return false;
    }

    public function fetch_recent_version() {
        // ex: https://api.github.com/repos/cwmoss/slowfoot/releases
        // https://api.github.com/repos/cwmoss/slowfoot/releases/latest
        // tag_name created_at 
        // assets: browser_download_url, digest, name
    }

    public function download_and_replace() {
    }
}
