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
                if (count(options >= 4)) return 3
                if (count(options >= 5)) return 7  # Unreachable due to >=4 above
                return 0
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
            if (count(options >= 5)) return 7
            if (count(options >= 4)) return 3
            if (count(options == 1)) return 1
            return 0
        EOL;

        $validator->validate($script);

        $this->assertTrue($validator->validate($script));
    }
}
