<?php

declare(strict_types=1);

namespace cwmoss\final_cli;

use CurlHandle;
use Exception;

class fetch {

    public bool $has_curl;

    /**
     * @param string[] $base_headers
     */
    public function __construct(public string $user_agent = 'final-cli-upgrade', public array $base_headers = []) {
        $this->has_curl = function_exists('curl_init');
    }

    private function curl_init_w_base_options(string $url, int $timeout = 30, array $headers = []): CurlHandle {
        $ch = curl_init();
        if (!$ch) throw new Exception("Could not init curl");
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $hdrs = array_merge($this->base_headers, $headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $hdrs);
        return $ch;
    }

    /**
     * @param string[] $headers
     */
    public function get(string $url, array $headers = []): string {
        if ($this->has_curl) {
            $ch = $this->curl_init_w_base_options($url, 30, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            return (string) $result;
        } else {
            $context = stream_context_create([
                'http' => [
                    'user_agent' => $this->user_agent,
                    'timeout' => 30,
                    'header' => array_merge($this->base_headers, $headers)
                ]
            ]);
            return (string) file_get_contents($url, false, $context);
        }
    }

    /**
     * @param string[] $headers
     */
    public function download_file(string $url, string|file $dest, array $headers = ["Accept: application/octet-stream"]): bool {
        if ($dest instanceof file) {
            $dest = $dest->fname;
        }
        $fp = fopen($dest, 'w');
        if (!$fp) return false;
        if ($this->has_curl) {
            $ch = $this->curl_init_w_base_options($url, 60, $headers);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($resource, int $dltotal, int $dlnow, int $ultotal, int $ulnow) {
                if ($dltotal > 0) {
                    $percent = (int) round($dlnow / $dltotal * 100);
                    $bar = str_repeat('█', (int)($percent / 2)) . str_repeat('░', 50 - (int)($percent / 2));
                    echo "\r[$bar] $percent%";
                }
            });
            $result = curl_exec($ch);
            echo "\n"; // newline after progress
        } else {
            $data = $this->get($url, $headers);
            if ($data === "") {
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
