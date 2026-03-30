<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use slowly\final_cli\command;
use slowly\final_cli\parser;

final class BaseTest extends TestCase {

    public function testArgument(): void {
        $fun = fn($_input) => $_input;
        $cmd = new command($fun, "translate");
        $parser = new parser(["dummy", "translate", "bible.la.txt"]);
        $res = $cmd->run($parser);
        $this->assertSame(["bible.la.txt"], $res[1]);

        $fun = fn($_input = "test.txt") => $_input;
        $cmd = new command($fun, "translate");
        $parser = new parser(["dummy", "translate"]);
        $res = $cmd->run($parser);
        $this->assertSame(["test.txt"], $res[1]);
    }

    public function testFlag(): void {
        $fun = fn(bool $force) => $force ? "y" : "n";
        $cmd = new command($fun, "translate");
        $parser = new parser(["dummy", "translate"]);
        $res = $cmd->run($parser);
        $this->assertSame([false], $res[1]);

        $parser = new parser(["dummy", "translate", "--force"]);
        $res = $cmd->run($parser);
        $this->assertSame([true], $res[1]);
    }
}
