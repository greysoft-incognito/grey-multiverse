<?php

namespace Tests\Feature;

use App\Services\PointsScript\PointsScriptParser;
use App\Services\PointsScript\PointsScriptValidator;
use Tests\TestCase;

class PointScriptTest extends TestCase
{
    private PointsScriptParser $parser;

    protected function setUp(): void
    {
        $ref = new \ReflectionClass($this);

        $this->allowed = collect($ref->getMethods(\ReflectionMethod::IS_PUBLIC))
            ->map(fn($e) => $e->getName())
            ->filter(fn($e) => str_starts_with($e, 'test'))
            ->toArray();


        parent::setUp();
        $this->parser = new PointsScriptParser();
    }

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
    public function testArrayScoring(): void
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
    public function testStringScoring(): void
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
    public function testStringDoesntContain(): void
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
    public function testStringDoesntContainButContains(): void
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

    public function testLengthGreaterThanOrEqual()
    {
        $script = <<<EOL
            if (length(>= 10)) give 5
            give 0
        EOL;
        $this->assertEquals(5, $this->parser->evaluate($script, "Hello World")); // 11 chars
        $this->assertEquals(0, $this->parser->evaluate($script, "Hi")); // 2 chars
        $this->assertEquals(5, $this->parser->evaluate($script, ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'])); // 10 items
    }

    public function testLengthLessThan()
    {
        $script = <<<EOL
            if (length(< 5)) give 3
            give 0
        EOL;
        $this->assertEquals(3, $this->parser->evaluate($script, "Hey")); // 3 chars
        $this->assertEquals(0, $this->parser->evaluate($script, "Hello World")); // 11 chars
        $this->assertEquals(3, $this->parser->evaluate($script, ['a', 'b', 'c'])); // 3 items
    }

    // Tests for !length()
    public function testNotLengthGreaterThan()
    {
        $script = <<<EOL
            if (!length(> 5)) give 2
            give 0
        EOL;
        $this->assertEquals(2, $this->parser->evaluate($script, "Hi")); // 2 chars, !(> 5) is true
        $this->assertEquals(0, $this->parser->evaluate($script, "Hello World")); // 11 chars, !(> 5) is false
        $this->assertEquals(2, $this->parser->evaluate($script, ['a', 'b', 'c'])); // 3 items, !(> 5) is true
    }

    // Tests for equals()
    public function testEqualsMatches()
    {
        $script = <<<EOL
            if (equals("yes")) give 2
            give 0
        EOL;
        $this->assertEquals(2, $this->parser->evaluate($script, "yes"));
        $this->assertEquals(0, $this->parser->evaluate($script, "no"));
        $this->assertEquals(0, $this->parser->evaluate($script, 123)); // Non-string scalar still compared as string
        $this->assertEquals(0, $this->parser->evaluate($script, ['yes'])); // Array not equal
    }

    // Tests for !equals()
    public function testNotEqualsDoesNotMatch()
    {
        $script = <<<EOL
            if (!equals("no")) give 3
            give 0
        EOL;
        $this->assertEquals(3, $this->parser->evaluate($script, "yes")); // Not "no"
        $this->assertEquals(0, $this->parser->evaluate($script, "no")); // Is "no"
        $this->assertEquals(3, $this->parser->evaluate($script, "maybe")); // Not "no"
    }

    // Tests for missing()
    public function testMissingIsNull()
    {
        $script = <<<EOL
            if (missing()) give 0
            give 1
        EOL;
        $this->assertEquals(0, $this->parser->evaluate($script, null)); // Null is missing
        $this->assertEquals(1, $this->parser->evaluate($script, "")); // Empty string not missing
        $this->assertEquals(1, $this->parser->evaluate($script, "yes")); // Non-null not missing
        $this->assertEquals(1, $this->parser->evaluate($script, [])); // Empty array not missing
    }

    // Tests for nothing()
    public function testNothingIsEmpty()
    {
        $script = <<<EOL
            if (nothing()) give 1
            give 0
        EOL;
        $this->assertEquals(1, $this->parser->evaluate($script, "")); // Empty string is nothing
        $this->assertEquals(1, $this->parser->evaluate($script, [])); // Empty array is nothing
        $this->assertEquals(0, $this->parser->evaluate($script, "yes")); // Non-empty string not nothing
        $this->assertEquals(0, $this->parser->evaluate($script, ['a'])); // Non-empty array not nothing
        $this->assertEquals(0, $this->parser->evaluate($script, null)); // Null not nothing
    }

    // Combined test for precedence and edge cases
    public function testCombinedConditions()
    {
        $script = <<<EOL
            if (missing()) give 0
            if (nothing()) give 1
            if (length(>= 10)) give 5
            if (equals("yes")) give 2
            give 3
        EOL;
        $this->assertEquals(0, $this->parser->evaluate($script, null)); // missing first
        $this->assertEquals(1, $this->parser->evaluate($script, "")); // nothing second
        $this->assertEquals(5, $this->parser->evaluate($script, "Hello World")); // length third
        $this->assertEquals(2, $this->parser->evaluate($script, "yes")); // equals fourth
        $this->assertEquals(3, $this->parser->evaluate($script, "maybe")); // default last
    }
    public function testMatchesExactCount()
    {
        $script = <<<EOL
            if (matches("Proprietary tech/IP", "cost reduction", "exclusive partnerships", == 3)) give 5
            give 0
        EOL;
        $this->assertEquals(5, $this->parser->evaluate($script, ["Proprietary tech/IP", "cost reduction", "exclusive partnerships"]));
        $this->assertEquals(0, $this->parser->evaluate($script, ["Proprietary tech/IP", "cost reduction"]));
        $this->assertEquals(0, $this->parser->evaluate($script, "Proprietary tech/IP"));
    }

    public function testMatchesAtLeast()
    {
        $script = <<<EOL
            if (matches("Proprietary tech/IP", "cost reduction", "exclusive partnerships", > 2)) give 5
            if (matches("Proprietary tech/IP", "cost reduction", "exclusive partnerships", == 2)) give 3
            if (matches("Proprietary tech/IP", "cost reduction", "exclusive partnerships", == 1)) give 1
            give 0
        EOL;
        $this->assertEquals(5, $this->parser->evaluate($script, ["Proprietary tech/IP", "cost reduction", "exclusive partnerships"]));
        $this->assertEquals(3, $this->parser->evaluate($script, ["Proprietary tech/IP", "cost reduction"]));
        $this->assertEquals(1, $this->parser->evaluate($script, ["Proprietary tech/IP"]));
        $this->assertEquals(0, $this->parser->evaluate($script, "No clear advantage"));
    }

    public function testProprietaryMatchesAtLeast()
    {
        $script = <<<EOL
            if (matches("Proprietary technology/IP", "25%+ cost reduction vs. alternatives", "Exclusive partnerships", == 3)) give 10
            if (matches("Proprietary technology/IP", "25%+ cost reduction vs. alternatives", "Exclusive partnerships", == 2)) give 7
            if (matches("Proprietary technology/IP", "25%+ cost reduction vs. alternatives", "Exclusive partnerships", == 1)) give 5
            if (equals("No clear advantage")) give 0
            give 0
        EOL;

        $this->assertEquals(10, $this->parser->evaluate($script, ["Proprietary technology/IP", "25%+ cost reduction vs. alternatives", "Exclusive partnerships"]));
        $this->assertEquals(7, $this->parser->evaluate($script, ["Proprietary technology/IP", "25%+ cost reduction vs. alternatives"]));
        $this->assertEquals(5, $this->parser->evaluate($script, ["Proprietary technology/IP"]));
        $this->assertEquals(0, $this->parser->evaluate($script, "No clear advantage"));
    }

    public function testCombinedConditionsWithMatches()
    {
        $script = <<<EOL
            if (matches("Proprietary tech/IP", "cost reduction", "exclusive partnerships", == 3)) give 5
            if (matches("Proprietary tech/IP", "cost reduction", "exclusive partnerships", >= 2)) give 3
            if (equals("No clear advantage")) give 0
            give 0
        EOL;
        $this->assertEquals(5, $this->parser->evaluate($script, ["Proprietary tech/IP", "cost reduction", "exclusive partnerships"]));
        $this->assertEquals(3, $this->parser->evaluate($script, ["Proprietary tech/IP", "cost reduction"]));
        $this->assertEquals(0, $this->parser->evaluate($script, "No clear advantage"));
        $this->assertEquals(0, $this->parser->evaluate($script, ["Proprietary tech/IP"]));
        $this->assertEquals(0, $this->parser->evaluate($script, []));
    }

    public function testAndCondition()
    {
        $script = <<<EOL
            if (length(>= 10) and contains("good")) give 5
            give 0
        EOL;
        $this->assertEquals(5, $this->parser->evaluate($script, "This is good stuff"));
        $this->assertEquals(0, $this->parser->evaluate($script, "Not good"));
        $this->assertEquals(0, $this->parser->evaluate($script, "This is bad stuff"));
    }

    public function testOrCondition()
    {
        $script = <<<EOL
            if (contains("good") or contains("great")) give 4
            give 0
        EOL;
        $this->assertEquals(4, $this->parser->evaluate($script, "This is good"));
        $this->assertEquals(4, $this->parser->evaluate($script, "This is great"));
        $this->assertEquals(0, $this->parser->evaluate($script, "This is bad"));
    }

    public function testAndOrPrecedence()
    {
        $script = <<<EOL
            if (length(>= 5) and contains("good") or equals("yes")) give 3
            give 0
        EOL;
        $this->assertEquals(3, $this->parser->evaluate($script, "Hello good")); // length >= 5 AND contains "good"
        $this->assertEquals(3, $this->parser->evaluate($script, "yes")); // OR equals "yes"
        $this->assertEquals(0, $this->parser->evaluate($script, "Hi bad")); // Neither
    }
    public function testMaxAnswerArrayWithMatches()
    {
        $script = <<<EOL
            if (matches("a", "c", >= 2)) give 5
            if (count(options >= 1)) give 3
            give 0
        EOL;
        $options = [['value' => 'a'], ['value' => 'b']];
        $result = $this->parser->getMaxAnswer($script, 'array', $options);
        $this->assertEquals(['a', 'b', 'c'], $result);
        $this->assertEquals(5, $this->parser->evaluate($script, $result));
    }

    public function testMaxAnswerArrayWithCount()
    {
        $script = <<<EOL
            if (count(options >= 5)) give 7
            if (count(options >= 3)) give 4
            give 0
        EOL;
        $options = [['value' => 'a'], ['value' => 'b']];
        $result = $this->parser->getMaxAnswer($script, 'array', $options);
        $this->assertEquals(['a', 'b', 'x', 'x', 'x'], $result);
        $this->assertEquals(7, $this->parser->evaluate($script, $result));
    }

    public function testMaxAnswerStringWithLengthAndContains()
    {
        $script = <<<EOL
            if (length(>= 10) and contains("good")) give 5
            if (equals("yes")) give 3
            give 0
        EOL;
        $result = $this->parser->getMaxAnswer($script, 'string', []);
        $this->assertEquals('goodxxxxxx', $result); // 10 chars: "good" + 6 x's
        $this->assertEquals(5, $this->parser->evaluate($script, $result));
    }

    public function testMaxAnswerStringWithEquals()
    {
        $script = <<<EOL
            if (equals("yes")) give 3
            if (contains("good")) give 2
            give 0
        EOL;
        $result = $this->parser->getMaxAnswer($script, 'string', []);
        $this->assertEquals('yes', $result);
        $this->assertEquals(3, $this->parser->evaluate($script, $result));
    }

    public function testMaxAnswerEmptyScript()
    {
        $resultArray = $this->parser->getMaxAnswer('', 'array', [['value' => 'a']]);
        $this->assertEquals([], $resultArray);

        $resultString = $this->parser->getMaxAnswer('', 'string', []);
        $this->assertEquals('', $resultString);
    }

    public function testMaxAnswerDefaultOnly()
    {
        $script = "give 5";
        $resultArray = $this->parser->getMaxAnswer($script, 'array', [['value' => 'a']]);
        $this->assertEquals([], $resultArray);
        $this->assertEquals(5, $this->parser->evaluate($script, $resultArray));

        $resultString = $this->parser->getMaxAnswer($script, 'string', []);
        $this->assertEquals('', $resultString);
        $this->assertEquals(5, $this->parser->evaluate($script, $resultString));
    }

    public function testMaxAnswerWithNoMatchingCondition()
    {
        $script = <<<EOL
            if (length(< 5)) give 2
            give 0
        EOL;
        $result = $this->parser->getMaxAnswer($script, 'string', []);
        $this->assertEquals('', $result);
        $this->assertEquals(2, $this->parser->evaluate($script, $result)); // length(0) < 5 is true
    }
}
