<?php

namespace Tests\Feature;

use App\Exception\NotificationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Service\AuthorizationService;
use App\Service\NotificationService;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(AuthorizationService::class, function ($mock) {
            $mock->shouldReceive('isAuthorized')->andReturn(true);
        });

        $this->mock(NotificationService::class, function ($mock) {
            $mock->shouldReceive('sendTransactionNotification')->andReturn();
        });
    }

    public function test_common_user_can_make_a_transaction(): void
    {
        $payer = User::factory()->create(['type' => 'common']);
        $payer->wallet()->create(['balance' => 200]);

        $payee = User::factory()->create();
        $payee->wallet()->create(['balance' => 50]);

        $transactionData = [
            'payer' => $payer->id,
            'payee' => $payee->id,
            'value' => 100,
        ];

        $response = $this->postJson('/transfer', $transactionData);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Transação realizada com sucesso.']);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $payer->id,
            'balance' => 100,
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $payee->id,
            'balance' => 150,
        ]);

        $this->assertDatabaseHas('transactions', [
            'payer' => $payer->id,
            'payee' => $payee->id,
            'value' => 100,
        ]);
    }

    public function test_shopkeeper_cannot_make_a_transaction(): void
    {
        $payer = User::factory()->create(['type' => 'shopkeeper']);
        $payer->wallet()->create(['balance' => 200]);

        $payee = User::factory()->create();
        $payee->wallet()->create(['balance' => 50]);

        $transactionData = [
            'payer' => $payer->id,
            'payee' => $payee->id,
            'value' => 100,
        ];

        $response = $this->postJson('/transfer', $transactionData);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Lojistas não podem realizar transferências.']);

        $this->assertDatabaseHas('wallets', ['user_id' => $payer->id, 'balance' => 200]);
        $this->assertDatabaseHas('wallets', ['user_id' => $payee->id, 'balance' => 50]);
    }

    public function test_transaction_fails_with_insufficient_balance(): void
    {
        $payer = User::factory()->create(['type' => 'common']);
        $payer->wallet()->create(['balance' => 50]);

        $payee = User::factory()->create();
        $payee->wallet()->create(['balance' => 50]);

        $transactionData = [
            'payer' => $payer->id,
            'payee' => $payee->id,
            'value' => 100,
        ];

        $response = $this->postJson('/transfer', $transactionData);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Saldo insuficiente para realizar a transação.']);
    }

    public function test_transaction_fails_if_not_authorized(): void
    {
        $this->mock(AuthorizationService::class, function ($mock) {
            $mock->shouldReceive('isAuthorized')->andReturn(false);
        });

        $payer = User::factory()->create(['type' => 'common']);
        $payer->wallet()->create(['balance' => 200]);

        $payee = User::factory()->create();
        $payee->wallet()->create(['balance' => 50]);

        $transactionData = [
            'payer' => $payer->id,
            'payee' => $payee->id,
            'value' => 100,
        ];

        $response = $this->postJson('/transfer', $transactionData);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Transferência não autorizada.']);
    }

    public function test_transaction_succeeds_but_notification_fails(): void
    {
        $this->mock(NotificationService::class, function ($mock) {
            $mock->shouldReceive('sendTransactionNotification')
                ->andThrow(new NotificationException('Falha ao enviar notificação.', 500));
        });

        $payer = User::factory()->create(['type' => 'common']);
        $payer->wallet()->create(['balance' => 200]);

        $payee = User::factory()->create();
        $payee->wallet()->create(['balance' => 50]);

        $transactionData = [
            'payer' => $payer->id,
            'payee' => $payee->id,
            'value' => 100,
        ];

        $response = $this->postJson('/transfer', $transactionData);

        $response->assertStatus(500)
            ->assertJson(['error' => 'Falha ao enviar notificação.']);

        // The transaction is not rolled back if notification fails.
        $this->assertDatabaseHas('wallets', ['user_id' => $payer->id, 'balance' => 100]);
        $this->assertDatabaseHas('wallets', ['user_id' => $payee->id, 'balance' => 150]);
    }

    public function test_transaction_validation_fails(): void
    {
        $response = $this->postJson('/transfer', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payer', 'payee', 'value']);
    }
}
