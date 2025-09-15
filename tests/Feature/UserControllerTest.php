<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_common_user(): void
    {
        $userData = [
            'full_name' => 'Test User',
            'document' => '12345678901',
            'email' => 'test@example.com',
            'password' => 'password',
            'type' => 'common',
        ];

        $response = $this->postJson('/users', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Usuário e carteira criados com sucesso.',
                'user' => [
                    'full_name' => 'Test User',
                    'email' => 'test@example.com',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $response->json('user.id'),
            'balance' => 0,
        ]);
    }

    public function test_can_create_a_shopkeeper_user(): void
    {
        $userData = [
            'full_name' => 'Test Shopkeeper',
            'document' => '12345678901234',
            'email' => 'shop@example.com',
            'password' => 'password',
            'type' => 'shopkeeper',
        ];

        $response = $this->postJson('/users', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Usuário e carteira criados com sucesso.',
                'user' => [
                    'full_name' => 'Test Shopkeeper',
                    'email' => 'shop@example.com',
                    'type' => 'shopkeeper',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'shop@example.com',
        ]);
    }

    public function test_create_user_validation_fails(): void
    {
        $response = $this->postJson('/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['full_name', 'document', 'email', 'password', 'type']);
    }

    public function test_can_add_balance_to_user(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson("/users/{$user->id}/balance", ['value' => 100.50]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Saldo adicionado com sucesso.',
                'wallet' => [
                    'user_id' => $user->id,
                    'balance' => 100.50,
                ]
            ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance' => 100.50,
        ]);
    }

    public function test_add_balance_validation_fails(): void
    {
        $user = User::factory()->create();

        $this->postJson("/users/{$user->id}/balance", ['value' => 'not-a-number'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['value']);

        $this->postJson("/users/{$user->id}/balance", ['value' => 0])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['value']);
    }
}