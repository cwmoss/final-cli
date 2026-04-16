<?php

namespace cwmoss\final_cli;

use Exception;
use Throwable;

class error extends Exception {

    static public function nice_output(Throwable $e, bool $with_trace = true) {
        $trace = "";
        if ($with_trace) {
            $trace = $e->getTraceAsString() . "\n";
        }
        return sprintf("%s:%s\n%s", $e->getFile(), $e->getLine(), $trace);
    }
}
