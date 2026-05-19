<?php

namespace Tests\Feature;

use Tests\TestCase;

class CodeQualityScanCommandTest extends TestCase
{
    public function test_code_quality_scan_runs_without_failing_when_fail_on_none_is_used(): void
    {
        $this->artisan('code:quality --fail-on=none --format=json')
            ->assertSuccessful();
    }

    public function test_code_quality_scan_can_fail_on_current_warnings(): void
    {
        $this->artisan('code:quality --fail-on=warning --format=json')
            ->assertFailed();
    }
}
