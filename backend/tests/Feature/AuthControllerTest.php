<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_correct_credentials_returns_a_bearer_token(): void
    {
        User::factory()->create(['email' => 'demo@buildingos.test', 'password' => Hash::make('secret123')]);

        $response = $this->postJson('/api/login', ['email' => 'demo@buildingos.test', 'password' => 'secret123']);

        $response->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
    }

    public function test_login_with_wrong_password_is_rejected(): void
    {
        User::factory()->create(['email' => 'demo@buildingos.test', 'password' => Hash::make('secret123')]);

        $this->postJson('/api/login', ['email' => 'demo@buildingos.test', 'password' => 'wrong'])
            ->assertStatus(422);
    }

    public function test_user_endpoint_requires_a_token(): void
    {
        $this->getJson('/api/user')->assertStatus(401);
    }

    public function test_token_from_login_can_call_the_protected_user_endpoint(): void
    {
        User::factory()->create(['email' => 'demo@buildingos.test', 'password' => Hash::make('secret123'), 'name' => 'Demo']);

        $login = $this->postJson('/api/login', ['email' => 'demo@buildingos.test', 'password' => 'secret123']);
        $token = $login->json('token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/user')
            ->assertOk()
            ->assertJson(['name' => 'Demo', 'email' => 'demo@buildingos.test']);
    }

    public function test_logout_revokes_the_token_so_it_no_longer_works(): void
    {
        User::factory()->create(['email' => 'demo@buildingos.test', 'password' => Hash::make('secret123')]);
        $token = $this->postJson('/api/login', ['email' => 'demo@buildingos.test', 'password' => 'secret123'])->json('token');

        $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/logout')->assertStatus(204);

        // RequestGuard 快取上一次解析出來的 user，同一個測試方法內連續
        // 打兩次不會重新解析，要強制它下一次重新查 DB 才能看到 token
        // 真的被刪除的效果。
        Auth::forgetGuards();

        $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/user')->assertStatus(401);
    }
}
