<?php

require_once __DIR__ . '/vendor/autoload.php';

use slowly\final_cli\app;
use slowly\final_cli\alias;
use slowly\final_cli\terminal;

error_reporting(E_ALL);

/**
 * file stats.
 * this is a <u>really cool</u> cli programm
 */
$cli = app::new('filestats')
    ->add_command(stats::class, "stats")
    ->add_command(stats2::class, "stats2");

print_r($cli);

print terminal::bold("hi there\n");

$s = new stats;
$s("innn", false, "more", "and more");

$rest_index = null;

print_r(getopt("h:", [], $rest_index));
$pos_args = array_slice($argv, $rest_index);
print_r($pos_args);
// print_r($rest);

print_r(parseParameters());

// terminal::test_colors();

$cli->help();

print "\n\e[1mfett \e[21m und normal\n";

function parseParameters($noopt = array()) {
    $result = array();
    $params = $GLOBALS['argv'];
    // could use getopt() here (since PHP 5.3.0), but it doesn't work relyingly
    reset($params);
    foreach ($params as $p) {
        if ($p[0] == '-') {
            $pname = substr($p, 1);
            $value = true;
            if ($pname[0] == '-') {
                // long-opt (--<param>)
                $pname = substr($pname, 1);
                if (strpos($p, '=') !== false) {
                    // value specified inline (--<param>=<value>)
                    list($pname, $value) = explode('=', substr($p, 2), 2);
                }
            }
            // check if next parameter is a descriptor or a value
            $nextparm = current($params);
            if (!in_array($pname, $noopt) && $value === true && $nextparm !== false && $nextparm[0] != '-') {
                $value = next($params);
            }
            $result[$pname] = $value;
        } else {
            // param doesn't belong to any option
            $result[] = $p;
        }
    }
    return $result;
}

class stats {

    /**
     * show stats.
     * show the <b>stats</b> of files
     */
    public function __invoke(
        #[alias("i")]
        string $input,
        bool $nice,
        ...$rest
    ) {
        print "input: $input ~ nice: $nice ~ " . join(", ", $rest) . "\n";
    }
}



class stats2 {

    /**
     * 
     * show new stats
     * 
     * show the <b>stats</b> of files in a new 
     * way. <u>much better</u> and well tested and
     * gluten free too.
     * isn't it very nice? yes, i hear you say.
     */
    public function __invoke(
        string $input,
        bool $nice = true,
        array $inputfiles,
        string $outdir
    ) {
        print "input: $input ~ nice: $nice ~ " . join(", ", $rest) . "\n";
    }
}
