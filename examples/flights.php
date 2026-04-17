<?php

require_once __DIR__ . '/../vendor/autoload.php';

use cwmoss\final_cli\app;
use cwmoss\final_cli\cli;
use cwmoss\final_cli\file;

error_reporting(E_ALL);

/**
 * file stats.
 * this is a <u>really cool</u> cli programm
 */
$cli = new app('filestats')
    // ->debug()
    ->add_command(stats::class, "stats")
    ->add_command(stats2::class, "stats2")
    ->add_command("flight")
    ->run($argv);

/**
 * lookup flights.
 * looking for available flights using the
 * aero world flight API
 */
function flight(
    #[cli(description: "destination airport code or city name")] string $to,
    DateTime $date = new DateTime(),
    $from = "Berlin",
    ?file $rev = null,
    array $_via = []
) {
    print "looking for flights from {$from} to {$to} on {$date->format('d.m.y')}";
    foreach ($_via as $v) {
        print "\n  via {$v}";
    }
    print "\n";
    if ($rev) {
        print(strrev($rev->get_contents()));
        print "\n";
    }
}


class stats {

    /**
     * show stats.
     * show the <b>stats</b> of files
     */
    public function __invoke(
        #[cli("-i")]
        string $input,
        bool $nice,
        array $_rest
    ) {
        var_dump($_rest);
        print "input: $input ~ nice: $nice ~ " .
            join(", ", $_rest) . "\n";
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

        string $inputfiles,
        string $outdir,
        bool $nice = true,
    ) {
        print "input: $input ~ nice: $nice ~ " . join(", ", [$inputfiles]) . "\n";
    }
}
