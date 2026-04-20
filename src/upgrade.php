<?php

declare(strict_types=1);

namespace cwmoss\final_cli;

/*

    self upgrade is possible for 
        - single binary distribution
        - phar file distribution
        
    currently only github projects are supported
    
    public repos:
        project: cwmoss/final-cli
        token: "" 

    private repos:
        project: https://aoi.xcxcxc.../repos/cwmoss/final-cli
        token: Fine-grained personal access tokens with repo READ contents permissions

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

    public string $base_url;

    public function __construct(
        public string $program_name,
        public string $current_version,
        public string $github_project = "",
        public string $github_token = "",
        public string $destination = ""
    ) {
        $this->set_base_url();
    }

    public function set_base_url() {
        $base = $this->github_project;
        if (!str_starts_with($base, "https://")) {
            $base = "https://api.github.com/repos/{$this->github_project}";
        }
        $this->base_url = rtrim($base, "/");
    }

    public function auth_headers(): array {
        if ($this->github_token) {
            return ["Authorization: Bearer {$this->github_token}"];
        }
        return [];
    }

    /**
     * upgrade from github to latest version.
     */
    public function __invoke(): void {
        $term = new terminal;
        $term->println("checking for new version");
        $new_version = $this->check_version();
        if (!$new_version) {
            $term->println("You're already on the latest version: <b>{$this->current_version}</b>");
            return;
        }
        $this->download_and_replace($new_version);
    }

    /**
     * @return false|array <string,string>
     */
    public function check_version(): false|array {
        $release = $this->fetch_recent_version();
        if (!$release) {
            return false;
        }
        $latest_version = ltrim((string)$release['tag_name'], 'v');
        $current = ltrim($this->current_version, 'v');
        if (version_compare($latest_version, $current, '>')) {
            return $release;
        }
        return false;
    }

    public function fetch_recent_version(): false|array {
        $url = "{$this->base_url}/releases/latest";
        $data = new fetch(base_headers: $this->auth_headers())->get($url);
        if (!$data) {
            return false;
        }
        // @mago-ignore analyzer:mixed-return-statement
        return json_decode($data, true);
    }

    public function download_and_replace(array $release): void {
        $term = new terminal;
        $term->println("Current version: {$this->current_version}");
        $term->println("New version: {$release['tag_name']}");
        [$os, $arch] = util::get_platform();
        $asset = null;
        $needs_phar = util::is_hosted_phar();
        // @var array <string,string> $a
        foreach ((array)$release['assets'] as $a) {
            if (!is_array($a)) continue;
            $name = (string) $a['name'];
            if ($needs_phar && $name === "{$this->program_name}.phar") {
                $asset = (array)$a;
                break;
            }
            if (!$needs_phar && str_starts_with($name, "{$this->program_name}-{$os}-{$arch}.")) {
                $asset = (array)$a;
                break;
            }
        }
        if (!$asset) {
            $term->println("<red>No suitable download found for your platform.</red>");
            return;
        }
        // $url = (string)($asset['browser_download_url'] ?? "");
        $url = (string)($asset['url'] ?? "");
        $asset_name = (string)($asset['name'] ?? "");
        // $asset_id = (string)($asset['id'] ?? "");
        $digest = (string)($asset['digest'] ?? "");
        $size = util::human_filesize((int)($asset['size'] ?? 0));

        $term->println("Start download {$size}: {$url}");
        $temp_file = file::new_tempfile(prefix: 'upgrade_');

        if (!new fetch(base_headers: $this->auth_headers())->download_file($url, $temp_file)) {
            $term->println("<red>Failed to download file.</red>");
            return;
        }

        $temp_file->check_digest($digest) or throw new error("digest verification failed.");

        if (str_ends_with($asset_name, '.phar')) {
            if (!rename($temp_file->fname, $this->destination)) {
                $term->println("<red>Failed to replace file.</red>");
                return;
            }
        } else {
            // extract
            $zip = new unzip($temp_file, str_ends_with($asset_name, '.zip') ? "zip" : "tgz")
                ->extract();

            $temp_dir = $zip->temp_dir;

            // find the binary
            $files = glob("$temp_dir/*");
            if ($files === false) {
                throw new error("Could not list extracted files.");
            }
            $binary = null;
            foreach ($files as $f) {
                if (is_file($f) && basename($f) === $this->program_name) {
                    $binary = $f;
                    break;
                }
            }
            if (!$binary && count($files) === 1 && is_file($files[0])) {
                $binary = $files[0];
            }
            if (!$binary) {
                $term->println("<red>Could not find binary in archive.</red>");
                // unlink($temp_file);
                // $this->rmdir_recursive($temp_dir);
                return;
            }
            $term->println("copy from $binary to {$this->destination}");
            if (!rename($binary, $this->destination)) {
                $term->println("<red>Failed to replace binary.</red>");
                return;
            }
            if ($os !== 'win') {
                chmod($this->destination, 0755);
            }
        }
        $term->println("<green>Successfully upgraded to {$release['tag_name']}</green>");
        if ($os == "macos") {
            $term->println("xattr -dr com.apple.quarantine {$this->destination}");
        }
    }
}
