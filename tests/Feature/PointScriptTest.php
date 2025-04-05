<?php

namespace Tests\Feature;

use App\Services\PointsScript\PointsScriptValidator;
use Tests\TestCase;

class PointScriptTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testPointsScriptFailsValidation(): void
    {
        $this->assertThrows(function () {
            $validator = new PointsScriptValidator();

            // Invalid script (overlapping conditions)
            $script = <<<EOL
                if (count(options >= 4)) give 3
                if (count(options >= 5)) give 7  # Unreachable due to >=4 above
                give 0
            EOL;

            $validator->validate($script); // Throws exception
        }, \InvalidArgumentException::class);
    }

    /**
     * A basic feature test example.
     */
    public function testPointsScriptValidates(): void
    {
        $validator = new PointsScriptValidator();

        // Valid script
        $script = <<<EOL
            if (count(options >= 5)) give 7
            if (count(options >= 4)) give 3
            if (count(options == 1)) give 1
            give 0
        EOL;

        $validator->validate($script);

        $this->assertTrue($validator->validate($script));
    }

    /**
     * A basic feature test example.
     */
    public function testPointsScriptCounter(): void
    {
        $parser = new \App\Services\PointsScript\PointsScriptParser();

        $script = <<<EOL
            if (count(options >= 5)) give 7
            if (count(options >= 4)) give 3
            if (count(options == 1)) give 1
            give 0
        EOL;

        $run = $parser->evaluate($script, ['Yes', 'No', "Free", "non"]);

        $this->assertEquals($run, 3);
    }

    /**
     * A basic feature test example.
     */
    public function testPointsScriptContains(): void
    {
        $parser = new \App\Services\PointsScript\PointsScriptParser();

        $script = <<<EOL
            if (contains("good")) give 5
            if (!contains("bad")) give 3
            give 0
        EOL;

        $run = $parser->evaluate($script, "John is a good boy");

        $this->assertEquals($run, 5);
    }

    /**
     * A basic feature test example.
     */
    public function testPointsScriptDoesntContain(): void
    {
        $parser = new \App\Services\PointsScript\PointsScriptParser();

        $script = <<<EOL
            if (!contains("bad")) give 30
            if (contains("good")) give 5
            give 0
        EOL;

        $run = $parser->evaluate($script, "John is a gold boy");

        $this->assertEquals($run, 30);
    }

    /**
     * A basic feature test example.
     */
    public function testPointsScriptDoesntContainButContains(): void
    {
        $parser = new \App\Services\PointsScript\PointsScriptParser();

        $script = <<<EOL
            if (!contains("bad")) give 30
            if (contains("good")) give 5
            give 0
        EOL;

        $run = $parser->evaluate($script, "John is a bad boy");

        $this->assertEquals($run, 0);
    }
}
