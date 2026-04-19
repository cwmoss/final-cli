<?php

declare(strict_types=1);

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
        public string $program_name,
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
        $this->download_and_replace($new_version);
    }

    public function check_version(): false|array {
        $release = $this->fetch_recent_version();
        if (!$release) {
            return false;
        }
        $latest_version = ltrim($release['tag_name'], 'v');
        $current = ltrim($this->current_version, 'v');
        if (version_compare($latest_version, $current, '>')) {
            return $release;
        }
        return false;
    }

    public function fetch_recent_version() {
        $url = "https://api.github.com/repos/{$this->github_project}/releases/latest";
        $data = new fetch()->get($url);
        if (!$data) {
            return false;
        }
        return json_decode($data, true);
    }

    public function download_and_replace(array $release) {
        $term = new terminal;
        $term->println("Current version: {$this->current_version}");
        $term->println("New version: {$release['tag_name']}");
        [$os, $arch] = util::get_platform();
        $asset = null;
        $needs_phar = util::is_hosted_phar();
        foreach ($release['assets'] as $a) {
            $name = $a['name'];
            if ($needs_phar && $name === "{$this->program_name}.phar") {
                $asset = $a;
                break;
            }
            if (!$needs_phar && str_starts_with($name, "{$this->program_name}-{$os}-{$arch}.")) {
                $asset = $a;
                break;
            }
        }
        if (!$asset) {
            $term->println("<red>No suitable download found for your platform.</red>");
            return;
        }
        $url = $asset['browser_download_url'];
        $term->println("Start download: {$url}");
        $temp_file = tempnam(sys_get_temp_dir(), 'upgrade_');
        if (!new fetch()->download_file($url, $temp_file)) {
            $term->println("<red>Failed to download file.</red>");
            return;
        }
        if (str_ends_with($asset['name'], '.phar')) {
            if (!rename($temp_file, $this->destination)) {
                $term->println("<red>Failed to replace file.</red>");
                unlink($temp_file);
                return;
            }
        } else {
            // extract
            $temp_dir = sys_get_temp_dir() . '/upgrade_extract_' . uniqid();
            mkdir($temp_dir);
            if (str_ends_with($asset['name'], '.zip')) {
                exec("unzip -q \"$temp_file\" -d \"$temp_dir\"", $output, $code);
            } elseif (str_ends_with($asset['name'], '.tar.gz')) {

                $cmd = "tar -x -z -f $temp_file -C $temp_dir";
                $term->println($cmd);
                exec($cmd, $output, $code);
            } else {
                $term->println("<red>Unsupported archive format.</red>");
                unlink($temp_file);
                rmdir($temp_dir);
                return;
            }
            if ($code !== 0) {
                $term->println("<red>Failed to extract archive.</red>");
                unlink($temp_file);
                $this->rmdir_recursive($temp_dir);
                return;
            }
            // find the binary
            $files = glob("$temp_dir/*");
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
                unlink($temp_file);
                $this->rmdir_recursive($temp_dir);
                return;
            }
            if (PHP_OS_FAMILY !== 'Windows') {
                chmod($this->destination, 0755);
            }
            // $this->rmdir_recursive($temp_dir);
        }
        // unlink($temp_file);
        $term->println("<green>Successfully upgraded to {$release['tag_name']}</green>");
        if ($os == "macos") {
            $term->println("xattr -dr com.apple.quarantine {$this->destination}");
        }
    }


    private function rmdir_recursive(string $dir): void {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            if (is_dir($path)) {
                $this->rmdir_recursive($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
