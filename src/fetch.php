<?php

declare(strict_types=1);

namespace cwmoss\final_cli;

use CurlHandle;

class fetch {

    public bool $has_curl;

    public function __construct(public string $user_agent = 'final-cli-upgrade') {
        $this->has_curl = function_exists('curl_init');
    }

    private function curl_init_w_base_options(string $url, int $timeout = 30): CurlHandle|false {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        return $ch;
    }

    public function get(string $url): bool|string {
        if ($this->has_curl) {
            $ch = $this->curl_init_w_base_options($url, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            return $result;
        } else {
            $context = stream_context_create([
                'http' => [
                    'user_agent' => $this->user_agent,
                    'timeout' => 30,
                ]
            ]);
            return file_get_contents($url, false, $context);
        }
    }

    public function download_file(string $url, string $dest): bool {
        $fp = fopen($dest, 'w');
        if (!$fp) return false;
        if ($this->has_curl) {
            $ch = $this->curl_init_w_base_options($url, 60);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($resource, $dltotal, $dlnow, $ultotal, $ulnow) {
                if ($dltotal > 0) {
                    $percent = (int) round($dlnow / $dltotal * 100);
                    $bar = str_repeat('█', (int)($percent / 2)) . str_repeat('░', 50 - (int)($percent / 2));
                    echo "\r[$bar] $percent%";
                }
            });
            $result = curl_exec($ch);
            echo "\n"; // newline after progress
        } else {
            $data = $this->get($url);
            if ($data === false) {
                fclose($fp);
                return false;
            }
            fwrite($fp, $data);
            $result = true;
        }
        fclose($fp);
        return $result !== false;
    }
}
