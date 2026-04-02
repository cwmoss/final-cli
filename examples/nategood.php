<?php
require_once __DIR__ . '/../vendor/autoload.php';

use slowly\final_cli\app;
use slowly\final_cli\cli;
use slowly\final_cli\terminal;

enum title: string {
    case Mister = 'Mr.';
    case Misses = 'Mrs.';
    case Miss  = 'Ms.';
}

/**
 * I'll be there to greet you.
 * 
 * this is the example app from 
 * <u>https://github.com/nategood/commando</u>
 */
$hello_cmd = new app("hello")
    ->add_command(function (
        #[cli("name", description: "A person's name")]
        $name,
        #[cli("-t --title", description: "When set, use this title to address the person")]
        ?title $title = null,
        #[cli("-c --capitalize", description: "Always capitalize the words in a name")]
        bool $cap = false,
        #[cli("-e --educate", description: "Level up")]
        int $educate = 0
    ) {
        $level = ['', 'Jr', 'esq', 'PhD'];
        if ($cap) $name = ucwords($name);
        $hello = sprintf(
            "Hello, %s%s%s!",
            $title ? $title->value . " " : "",
            $name,
            $educate ? " " . $level[$educate] : ""
        );
        terminal::println($hello);
    })
    ->run($argv);
