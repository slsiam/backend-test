<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Redirect;
use Carbon\Carbon;

class AccessStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function testUniqueAccessCount()
    {
        $redirect = Redirect::factory()->create(['url' => 'https://example.com']);
        $ip = '192.168.1.1';

        // Simule dois acessos do mesmo IP
        $redirect->redirectLogs()->create([
            'redirect_id' => $redirect->id,
            'ip_address' => $ip,
        ]);

        $redirect->redirectLogs()->create([
            'redirect_id' => $redirect->id,
            'ip_address' => $ip,
        ]);

        // Obtenha as estatísticas de acesso para o redirecionamento
        $response = $this->get("/api/redirects/{$redirect->code}/stats");

        // Verifique se o total de acessos únicos é igual a 1
        $response->assertJsonFragment(['unique' => 1]);
    }

    public function testReferrerCount()
    {
        $redirect = Redirect::factory()->create(['url' => 'https://example.com']);
        $referrer1 = 'https://referrer1.com';
        $referrer2 = 'https://referrer2.com';
        $ip = '192.168.1.1';

        // Simule dois acessos com diferentes referenciadores
        $redirect->redirectLogs()->create([
            'redirect_id' => $redirect->id,
            'referer' => $referrer1,
            'ip_address' => $ip,

        ]);
        $redirect->redirectLogs()->create([
            'redirect_id' => $redirect->id,
            'referer' => $referrer2,
            'ip_address' => $ip,

        ]);
        $redirect->redirectLogs()->create([
            'redirect_id' => $redirect->id,
            'referer' => $referrer2,
            'ip_address' => $ip,

        ]);

        // Obtenha as estatísticas de acesso para o redirecionamento
        $response = $this->get("/api/redirects/{$redirect->code}/stats");


        // Verifique se os referenciadores estão contados corretamente
        $response->assertJsonFragment([
            'top_referrers' => [
                ['count' => 2, 'referer' => $referrer2],
                ['count' => 1, 'referer' => $referrer1],
            ]
        ]);
    }

    public function testAccessLast10Days()
    {
        $redirect = Redirect::factory()->create(['url' => 'https://example.com']);
        $accessDates = [
            Carbon::today()->subDays(2)->toDateString(),
            Carbon::today()->subDays(5)->toDateString(),
            Carbon::today()->subDays(8)->toDateString(),
        ];

        $ip = '192.168.1.1';

        foreach ($accessDates as $date) {
            $redirect->redirectLogs()->create([
                'redirect_id' => $redirect->id,
                'created_at' => $date,
                'ip_address' => $ip,
            ]);
        }

        $response = $this->get("/api/redirects/{$redirect->code}/stats");

        $response->assertJsonFragment([
            'last_10_days' => [
                ['date' => $accessDates[0], 'total' => 1, 'unique' => 1],
                ['date' => $accessDates[1], 'total' => 1, 'unique' => 1],
                ['date' => $accessDates[2], 'total' => 1, 'unique' => 1],
            ]
        ]);
    }

    public function testNoAccessLast10Days()
    {
        $redirect = Redirect::factory()->create(['url' => 'https://example.com']);

        // Obtenha as estatísticas de acesso para o redirecionamento
        $response = $this->get("/api/redirects/{$redirect->code}/stats");

        // Verifique se os acessos dos últimos 10 dias estão corretos quando não há acessos
        $response->assertJsonFragment(['last_10_days' => []]);
    }
    public function testAccessLast10DaysWithAccess()
    {
        $redirect = Redirect::factory()->create(['url' => 'https://example.com']);
        $ip = '192.168.1.1';

        $startDate = now()->subDays(9); // Start date is 9 days ago
        $endDate = now(); // End date is today
        for ($date = $startDate; $date <= $endDate; $date = $date->addDay()) {
            $redirect->redirectLogs()->create([
                'redirect_id' => $redirect->id,
                'created_at' => $date,
                'ip_address' => $ip,

            ]);
        }

        $response = $this->get("/api/redirects/{$redirect->code}/stats");
        $response->assertStatus(200);

        $responseData = $response->json();

        $this->assertArrayHasKey('last_10_days', $responseData);
        $this->assertCount(10, $responseData['last_10_days']);

    }


    public function testAccessLast10DaysWithSomeDaysMissing()
    {
        $redirect = Redirect::factory()->create(['url' => 'https://example.com']);
        $accessDates = [
            Carbon::today()->subDays(2)->toDateString(),
            Carbon::today()->subDays(5)->toDateString(),
            Carbon::today()->subDays(12)->toDateString(),
        ];
        $ip = '192.168.1.1';

        foreach ($accessDates as $date) {

            $redirect->redirectLogs()->create([
                'redirect_id' => $redirect->id,
                'created_at' => $date,
                'ip_address' => $ip,

            ]);
        }

        $response = $this->get("/api/redirects/{$redirect->code}/stats");

        $response->assertJsonFragment([
            'last_10_days' => [
                ['date' => $accessDates[0], 'total' => 1, 'unique' => 1],
                ['date' => $accessDates[1], 'total' => 1, 'unique' => 1],
            ]
        ]);
    }

}
