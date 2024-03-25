<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Redirect;

class QueryParamsValidationTest extends TestCase
{
    public function testMergeQueryParamsFromRequestAndRedirect()
    {
        $redirect = Redirect::factory()->create([
            'url' => 'https://example.com?utm_source=facebook&utm_campaign=ads',
        ]);

        $response = $this->get("/r/{$redirect->code}?utm_source=instagram&utm_campaign=ads");
        $response->assertRedirect('https://example.com?utm_source=instagram&utm_campaign=ads');
    }
    public function testPrioritizeQueryParamsFromRequest()
    {
        $redirect = Redirect::factory()->create([
            'url' => 'https://example.com?utm_source=facebook&utm_campaign=ads',
        ]);

        $response = $this->get("/r/{$redirect->code}?utm_source=instagram&utm_campaign=ads");
        $response->assertRedirect('https://example.com?utm_source=instagram&utm_campaign=ads');
    }

    public function testMergeIgnoringEmptyQueryParamsInRequest()
    {
        $redirect = Redirect::factory()->create([
            'url' => 'https://example.com?utm_source=facebook',
        ]);

        $response = $this->get("/r/{$redirect->code}?utm_source=&utm_campaign=test");
        $response->assertRedirect('https://example.com?utm_source=facebook&utm_campaign=test');
    }
}
