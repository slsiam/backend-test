<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RedirectApiTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateRedirectWithValidURL()
    {
        $response = $this->json('POST', '/api/redirects', ['url' => 'https://example.com']);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('redirects', [
            'url' => 'https://example.com',
        ]);
    }

    public function testCreateRedirectWithInvalidDNS()
    {
        $response = $this->postJson('/api/redirects', ['url' => 'https://nonexistent-domain-123.com']);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testCreateRedirectWithInvalidURL()
    {
        $response = $this->postJson('/api/redirects', ['url' => 'invalid-url']);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testCreateRedirectWithURLPointingToApplication()
    {
        $response = $this->postJson('/api/redirects', ['url' => url('/api/redirects')]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testCreateRedirectWithoutHTTPS()
    {
        $response = $this->postJson('/api/redirects', ['url' => 'http://example.com']);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testCreateRedirectWithURLReturningNon200Or201Status()
    {
        // Mock a URL that returns a 404 status
        $response = $this->postJson('/api/redirects', ['url' => 'https://example.com/non-existent-page']);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testCreateRedirectWithEmptyQueryParamsKey()
    {
        $response = $this->postJson('/api/redirects', ['url' => 'https://example.com?utm_source=']);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

}
