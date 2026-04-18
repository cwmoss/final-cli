<?php

namespace cwmoss\final_cli;


class upgrade {

    public function __construct(public string $current_version, public string $github_project = "") {
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
    }

    public function download_and_replace() {
    }
}
