<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VisitorLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VisitorControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeVisitor(array $overrides = []): VisitorLog
    {
        return VisitorLog::create(array_merge([
            'visitor_name' => '王小明',
            'visitor_type' => 'guest',
            'target_unit' => 'A-1203',
            'status' => 'left',
            'registered_at' => now()->subHours(2),
            'notified_at' => now()->subHours(2)->addMinutes(1),
        ], $overrides));
    }

    private function bearerToken(): string
    {
        User::factory()->create(['email' => 'demo@buildingos.test', 'password' => Hash::make('secret123')]);

        return $this->postJson('/api/login', ['email' => 'demo@buildingos.test', 'password' => 'secret123'])->json('token');
    }

    public function test_reset_demo_requires_authentication(): void
    {
        $visitor = $this->makeVisitor();

        $this->postJson('/api/visitors/reset-demo')->assertStatus(401);

        $this->assertSame('left', $visitor->fresh()->status);
    }

    public function test_reset_demo_randomizes_statuses_when_all_were_the_same(): void
    {
        foreach (range(1, 8) as $i) {
            $this->makeVisitor(['visitor_name' => "訪客 {$i}"]);
        }
        $token = $this->bearerToken();

        $response = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/visitors/reset-demo');

        $response->assertOk();
        $statusesAfter = VisitorLog::pluck('status')->unique();
        $this->assertGreaterThan(1, $statusesAfter->count(), '8 筆訪客隨機打散到 4 種狀態，不應該還是全部同一種');
    }

    public function test_reset_demo_clears_notified_at_for_visitors_reset_to_waiting(): void
    {
        foreach (range(1, 8) as $i) {
            $this->makeVisitor(['visitor_name' => "訪客 {$i}"]);
        }
        $token = $this->bearerToken();

        $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/visitors/reset-demo')->assertOk();

        $waiting = VisitorLog::where('status', 'waiting')->get();
        foreach ($waiting as $visitor) {
            $this->assertNull($visitor->notified_at);
        }
    }
}
