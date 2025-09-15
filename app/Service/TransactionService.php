<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\TransactionException;
use App\Models\User;
use App\Models\Wallet;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransactionService
{
    private AuthorizationService $authServiceClient;

    public function __construct(AuthorizationService $authServiceClient)
    {
        $this->authServiceClient = $authServiceClient;
    }

    public function addBalanceToUserWallet(int $userId, float $value): array
    {
        try {
            DB::beginTransaction();

            $user = User::find($userId);

            if (!$user) {
                throw new TransactionException('Usuário não encontrado.', 404);
            }

            $wallet = $user->wallet;

            if (!$wallet) {
                $wallet = new Wallet(['user_id' => $userId, 'balance' => 0.0]);
                $user->wallet()->save($wallet);
            }

            $wallet->balance += $value;
            $wallet->save();

            DB::commit();

            return [
                'message' => 'Saldo adicionado com sucesso.',
                'wallet' => $wallet->toArray(),
            ];
        } catch (TransactionException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            throw new TransactionException('Ocorreu um erro inesperado ao adicionar saldo.', 500, $e);
        }
    }

    public function processTransaction(int $payerId, int $payeeId, float $value): array
    {
        try {
            DB::beginTransaction();

            $payer = User::find($payerId);
            $payee = User::find($payeeId);

            if (!$payer || !$payee) {
                throw new TransactionException('Pagador ou recebedor não encontrado.', 404);
            }

            if ($payer->type === 'shopkeeper') {
                throw new TransactionException('Lojistas não podem realizar transferências.', 403);
            }

            if ($payer->wallet->balance < $value) {
                throw new TransactionException('Saldo insuficiente para realizar a transação.', 400);
            }

            if (!$this->authServiceClient->isAuthorized()) {
                throw new TransactionException('Transferência não autorizada.', 403);
            }

            $payer->wallet->balance -= $value;
            $payee->wallet->balance += $value;

            $payer->wallet->save();
            $payee->wallet->save();

            $transaction = $payer->transactions()->create([
                'payee' => $payeeId,
                'value' => $value,
            ]);

            DB::commit();

            return [
                'message' => 'Transação realizada com sucesso.',
                'transaction' => $transaction,
                'payer_wallet' => $payer->wallet,
                'payee_wallet' => $payee->wallet,
                'payer' => $payer,
                'payee' => $payee,
                'value' => $value,
            ];
        } catch (TransactionException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            throw new TransactionException('Ocorreu um erro inesperado ao processar a transação.', 500, $e);
        }
    }

}
